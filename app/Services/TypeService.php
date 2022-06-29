<?php


namespace App\Services;

use App\Models\Okapi\Field;
use App\Models\Okapi\Relationship;
use Exception;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;

use App\Models\Okapi\Type;
use Illuminate\Support\Str;

class TypeService
{
    public static function getForeignKeyNameForRelationship(Relationship $relationship): string
    {
        return "{$relationship->slug}_id";
    }

    public static function getReverseForeignKeyNameForRelationship(Relationship $relationship): string
    {
        return "{$relationship->reverse_slug}_id";
    }

    public static function getTableNameForType(Type $type): string
    {
        return Str::plural($type->slug);
    }

    public static function getManyToManyTableNameForRelationship(Relationship $relationship): string
    {
        /** @var Type $type */
        $type = $relationship->fromType()->firstOrFail();
        /** @var Type $related */
        $related = $relationship->toType()->firstOrFail();

        return implode('_', [
            self::getTableNameForType($type),
            self::getTableNameForType($related),
            Str::lower($relationship->name),
        ]);
    }

    protected function treatExternalRelationships(Collection $relationships, Type $type): void
    {
        /** @var Relationship $relationship */
        foreach ($relationships as $relationship) {
            /** @var Type $related */
            $related = $relationship->toType()->firstOrFail();
            if ($relationship->type === 'has one') {
                Schema::table(self::getTableNameForType($related),
                    static function (Blueprint $table) use ($type, $relationship) {
                        $table->foreignId(self::getReverseForeignKeyNameForRelationship($relationship))
                            ->nullable()
                            ->unique()
                            ->references('id')
                            ->on(self::getTableNameForType($type))
                            ->nullOnDelete();
                    });
            } elseif ($relationship->type === 'has many') {
                Schema::table(self::getTableNameForType($related),
                    static function (Blueprint $table) use ($type, $relationship) {
                        $table->foreignId(self::getReverseForeignKeyNameForRelationship($relationship))
                            ->nullable()
                            ->references('id')
                            ->on(self::getTableNameForType($type))
                            ->nullOnDelete();
                    });
            } elseif ($relationship->type === 'belongs to many') {
                Schema::create(self::getManyToManyTableNameForRelationship($relationship),
                    static function (Blueprint $table) use ($type, $related, $relationship) {
                        $table->id();
                        $table->foreignId(self::getReverseForeignKeyNameForRelationship($relationship))
                            ->nullable()
                            ->references('id')
                            ->on(self::getTableNameForType($type))
                            ->cascadeOnDelete();
                        $table->foreignId(self::getForeignKeyNameForRelationship($relationship))
                            ->nullable()
                            ->references('id')
                            ->on(self::getTableNameForType($related))
                            ->cascadeOnDelete();
                        $table->unique([
                            self::getForeignKeyNameForRelationship($relationship),
                            self::getReverseForeignKeyNameForRelationship($relationship)
                        ]);
                        $table->timestamps();
                    });
            }
        }
    }

    protected function createNewFieldsForType(Collection $fields, Collection $relationships, Blueprint $table): void
    {
        $availableFields = Config::get('okapi.available_fields');

        /** @var Field $field */
        foreach ($fields as $field) {
            $function = $availableFields[$field->type]['column_type'];
            $table->{$function}($field->slug)->nullable();
        }

        /** @var Relationship $relationship */
        foreach ($relationships as $relationship) {
            if ($relationship->type === 'belongs to one') {
                /** @var Type $related */
                $related = $relationship->toType()->firstOrFail();
                $table->foreignId(self::getForeignKeyNameForRelationship($relationship))
                    ->nullable()
                    ->references('id')
                    ->on(self::getTableNameForType($related))
                    ->nullOnDelete();
            }
        }
    }

    public function createTableUsingType(Type $type): void
    {
        Schema::create(self::getTableNameForType($type), function (Blueprint $table) use ($type) {
            $table->id();

            $this->createNewFieldsForType($type->fields()->get(), $type->relationships()->get(), $table);

            $table->foreignId('created_by')
                ->nullable()
                ->references('id')
                ->on('users')
                ->nullOnDelete();
            $table->foreignId('updated_by')
                ->nullable()
                ->references('id')
                ->on('users')
                ->nullOnDelete();
            $table->timestamps();
        });

        $this->treatExternalRelationships($type->relationships()->get(), $type);
    }

    public function updateTableUsingType(Type $type, Collection $newFields, Collection $newRelationships): void
    {
        Schema::table(self::getTableNameForType($type), function (Blueprint $table) use ($newFields, $newRelationships) {
            $this->createNewFieldsForType($newFields, $newRelationships, $table);
        });

        $this->treatExternalRelationships($newRelationships, $type);
    }

    public function dropTableForType(Type $type): void
    {
        Schema::table(self::getTableNameForType($type), static function (Blueprint $table) {
            $table->dropIfExists();
        });
    }

    public function cleanLeftoverFields(Type $type, Collection $fields): void
    {
        Schema::table(self::getTableNameForType($type), static function (Blueprint $table) use ($fields) {
            $table->dropColumn($fields->map(fn($field) => $field->slug)->toArray());
        });
    }

    public function cleanLeftoverRelationships(Type $type, Collection $relationships): void
    {
        /** @var Relationship $relationship */
        foreach ($relationships as $relationship) {
            /** @var Type $related */
            $related = $relationship->toType()->firstOrFail();
            if ($relationship->type === 'belongs to one') {
                Schema::table(self::getTableNameForType($type), static function (Blueprint $table) use ($relationship) {
                    $table->dropConstrainedForeignId(self::getForeignKeyNameForRelationship($relationship));
                });
            } elseif ($relationship->type === 'has one' || $relationship->type === 'has many') {
                Schema::table(self::getTableNameForType($related), static function (Blueprint $table) use ($relationship) {
                    $table->dropConstrainedForeignId(self::getReverseForeignKeyNameForRelationship($relationship));
                });
            } elseif ($relationship->type === 'belongs to many') {
                Schema::dropIfExists(self::getManyToManyTableNameForRelationship($relationship));
            }
        }
    }
}
