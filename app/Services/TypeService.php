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
    public static function getForeignKeyNameForType(Type $type): string
    {
        return "{$type->slug}_id";
    }

    public static function getTableNameForType(Type $type): string
    {
        return Str::plural($type->slug);
    }

    public static function getManyToManyTableNameForTypes(Type $type, Type $related): string
    {
        return self::getTableNameForType($type) . '_' . self::getTableNameForType($related);
    }

    protected function treatExternalRelationships(Collection $relationships, Type $type): void
    {
        /** @var Relationship $relationship */
        foreach ($relationships as $relationship) {
            /** @var Type $related */
            $related = $relationship->toType()->get();
            if ($relationship->type === 'has one') {
                Schema::table(self::getTableNameForType($related),
                    static function (Blueprint $table) use ($type) {
                        $table->foreignId(self::getForeignKeyNameForType($type))
                            ->nullable()
                            ->unique()
                            ->references('id')
                            ->references(self::getTableNameForType($type))
                            ->nullOnDelete();
                    });
            } elseif ($relationship->type === 'has many') {
                Schema::table(self::getTableNameForType($related),
                    static function (Blueprint $table) use ($type) {
                        $table->foreignId(self::getForeignKeyNameForType($type))
                            ->nullable()
                            ->references('id')
                            ->references(self::getTableNameForType($type))
                            ->nullOnDelete();
                    });
            } elseif ($relationship->type === 'belongs to many') {
                Schema::create(self::getManyToManyTableNameForTypes($type, $related),
                    static function (Blueprint $table) use ($type, $related) {
                        $table->foreignId(self::getForeignKeyNameForType($type))
                            ->nullable()
                            ->references('id')
                            ->references(self::getTableNameForType($type))
                            ->cascadeOnDelete();
                        $table->foreignId(self::getForeignKeyNameForType($related))
                            ->nullable()
                            ->references('id')
                            ->references(self::getTableNameForType($related))
                            ->cascadeOnDelete();
                        $table->unique(
                            self::getForeignKeyNameForType($type),
                            self::getForeignKeyNameForType($related)
                        );
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
                $related = $relationship->toType()->get();
                $table->foreignId(self::getForeignKeyNameForType($related))
                    ->nullable()
                    ->references('id')
                    ->references(self::getTableNameForType($related))
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
            $related = $relationship->toType()->get();
            if ($relationship->type === 'belongs to one') {
                Schema::table(self::getTableNameForType($type), static function (Blueprint $table) use ($related) {
                    $table->dropConstrainedForeignId(self::getForeignKeyNameForType($related));
                });
            } elseif ($relationship->type === 'has one' || $relationship->type === 'has many') {
                Schema::table(self::getTableNameForType($related), static function (Blueprint $table) use ($type) {
                    $table->dropConstrainedForeignId(self::getForeignKeyNameForType($type));
                });
            } elseif ($relationship->type === 'belongs to many') {
                Schema::dropIfExists(self::getManyToManyTableNameForTypes($type, $related));
            }
        }
    }
}
