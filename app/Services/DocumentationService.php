<?php

namespace App\Services;

use App\Models\Okapi\ApiKey;
use App\Models\Okapi\Field;
use App\Models\Okapi\Relationship;
use App\Models\Okapi\Type;
use App\Models\Role;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use JetBrains\PhpStorm\ArrayShape;
use Spatie\Permission\Models\Permission;

class DocumentationService
{
    protected const OUTPUT_DEFINITION_TYPES = [
        'string' => 'string',
        'text' => 'string',
        'email' => 'string',
        'password' => 'string',
        'integer' => 'integer',
        'double' => 'number',
        'enum' => 'string',
        'date' => 'string',
        'hour' => 'string',
        'file' => 'string',
        'boolean' => 'boolean',
    ];

    protected const METHOD_TO_FUNCTION = [
        'index' => 'generateListPath',
        'show' => 'generateViewPath',
        'store' => 'generateCreatePath',
        'update' => 'generateEditPath',
        'destroy' => 'generateDeletePath',
    ];

    private function getResponseObjectNameForType(Type $type): string
    {
        return $type->slug . '-response';
    }

    private function getRequestObjectNameForType(Type $type): string
    {
        return $type->slug . '-request';
    }

    #[ArrayShape(['type' => "string", 'properties' => "array"])] private function generateTypeDefinition(Type $type, bool $request = true): array
    {
        $definition = [
            'type' => 'object',
            'properties' => [],
        ];

        /** @var Field $field */
        foreach ($type->fields as $field) {
            $properties = [
                'type' => self::OUTPUT_DEFINITION_TYPES[$field->type],
            ];

            if ($field->type === 'enum') {
                $properties['enum'] = $field->properties->options;
            }

            if ($field->type === 'file' && $request) {
                $properties = [
                    'type' => 'string',
                    'format' => 'binary',
                ];
            }

            $definition['properties'][$field->slug] = $properties;
        }

        /** @var Relationship $relationship */
        foreach ($type->relationships()->with('toType')->get() as $relationship) {
            $definition['properties'][TypeService::getForeignKeyNameForRelationship($relationship)] =
                in_array($relationship->type, ['belongs to many', 'has many']) ? [
                    'type' => 'array',
                    'items' => [
                        'type' => 'integer',
                    ],
                ] : [
                    'type' => 'integer',
                ];
        }

        /** @var Relationship $relationship */
        foreach ($type->reverseRelationships()->with('toType')->get() as $relationship) {
            if ($relationship->reverse_visible) {
                $definition['properties'][TypeService::getReverseForeignKeyNameForRelationship($relationship)] =
                    in_array($relationship->type, ['belongs to many', 'belongs to one']) ? [
                        'type' => 'array',
                        'items' => [
                            'type' => 'integer',
                        ],
                    ] : [
                        'type' => 'integer',
                    ];
            }
        }

        return $definition;
    }

    /**
     * @param Type $type
     * @param string $method
     * @return Permission|null
     */
    private function getPermissionForTypeAndMethod(Type $type, string $method): ?Permission
    {
        return $type->permissions()->where('name', 'like', "%.$method")->first();
    }

    private function checkIfMethodIsAllowed(Type $type, string $method): bool
    {
        $permission = $this->getPermissionForTypeAndMethod($type, $method);
        return $permission?->roles()->exists() ||
            DB::table('model_has_permissions')
                ->where('permission_id', $permission?->id)
                ->where('model_type', ApiKey::class)->exists();
    }

    private function checkPermissionsAndAddSecurity(Type $type, array &$definition, string $method): void
    {
        $permission = $this->getPermissionForTypeAndMethod($type, $method);
        /** @var Permission $permission */
        if ($permission) {
            if ($permission->roles()->count() && $permission->roles()->where('name', '=', Role::PUBLIC_ROLE)->doesntExist()) {
                $definition['security'][] = ['bearerAuth' => [],];
            }

            if (DB::table('model_has_permissions')
                ->where('permission_id', $permission?->id)
                ->where('model_type', ApiKey::class)->exists()) {
                $definition['security'][] = ['apiKeyAuth' => [],];
            }


            if (isset($definition['security'])) {
                $definition['responses']['403'] = [
                    'description' => 'Action not allowed',
                ];
            }
        }
    }

    #[ArrayShape(['get' => "array"])] private function generateListPath(Type $type): array
    {
        $definition = [
            'tags' => [$type->slug],
            'operationId' => Str::slug('list ' . $type->slug),
            'summary' => 'View all ' . $type->name . ' instances',
            'responses' => [
                '200' => [
                    'description' => 'OK',
                    'content' => ['application/json' => ['schema' => [
                        'type' => 'array',
                        'items' => [
                            '$ref' => '#/components/schemas/' . $this->getResponseObjectNameForType($type),
                        ]
                    ],],],
                ],
            ],
        ];

        $this->checkPermissionsAndAddSecurity($type, $definition, 'list');
        return [
            'get' => $definition
        ];
    }

    #[ArrayShape(['get' => "array"])] private function generateViewPath(Type $type): array
    {
        $definition = [
            'tags' => [$type->slug],
            'operationId' => Str::slug('view ' . $type->slug),
            'summary' => 'View a ' . $type->name . ' instance',
            'parameters' => [[
                'name' => 'id',
                'in' => 'path',
                'description' => 'ID of the ' . $type->name . ' you want to view',
                'required' => true,
                'schema' => ['type' => 'integer',],
            ],],
            'responses' => [
                '200' => [
                    'description' => 'OK',
                    'content' => ['application/json' => ['schema' => [
                        '$ref' => '#/components/schemas/' . $this->getResponseObjectNameForType($type),
                    ],],],
                ],
            ],
        ];

        $this->checkPermissionsAndAddSecurity($type, $definition, 'view');
        return [
            'get' => $definition
        ];
    }

    #[ArrayShape(['patch' => "array"])] private function generateEditPath(Type $type): array
    {
        $definition = [
            'tags' => [$type->slug],
            'operationId' => Str::slug('edit ' . $type->slug),
            'summary' => 'Update a ' . $type->name . ' instance',
            'requestBody' => ['content' => ['application/json' => ['schema' => [
                '$ref' => '#/components/schemas/' . $this->getRequestObjectNameForType($type),
            ],],],],
            'parameters' => [[
                'name' => 'id',
                'in' => 'path',
                'description' => 'ID of the ' . $type->name . ' you want to update',
                'required' => true,
                'schema' => ['type' => 'integer',],
            ],],
            'responses' => [
                '200' => [
                    'description' => 'OK',
                    'content' => ['application/json' => ['schema' => [
                        '$ref' => '#/components/schemas/' . $this->getResponseObjectNameForType($type),
                    ],],],
                ],
            ],
        ];

        $this->checkPermissionsAndAddSecurity($type, $definition, 'edit');
        return [
            'patch' => $definition
        ];
    }

    #[ArrayShape(['delete' => "array"])] private function generateDeletePath(Type $type): array
    {
        $definition = [
            'tags' => [$type->slug],
            'operationId' => Str::slug('delete ' . $type->slug),
            'summary' => 'Delete a ' . $type->name . ' instance',
            'parameters' => [[
                'name' => 'id',
                'in' => 'path',
                'description' => 'ID of the ' . $type->name . ' you want to delete',
                'required' => true,
                'schema' => ['type' => 'integer',],
            ],],
            'responses' => [
                '200' => [
                    'description' => 'OK',
                    'content' => ['application/json' => ['schema' => [
                        '$ref' => '#/components/schemas/' . $this->getResponseObjectNameForType($type),
                    ],],],
                ],
            ],
        ];

        $this->checkPermissionsAndAddSecurity($type, $definition, 'delete');
        return [
            'delete' => $definition
        ];
    }

    #[ArrayShape(['post' => "array"])] private function generateCreatePath(Type $type): array
    {
        $definition = [
            'tags' => [$type->slug],
            'operationId' => Str::slug('create ' . $type->slug),
            'summary' => 'Add a new ' . $type->name . ' instance',
            'requestBody' => ['content' => ['application/json' => ['schema' => [
                '$ref' => '#/components/schemas/' . $this->getRequestObjectNameForType($type),
            ],],],],
            'responses' => [
                '200' => [
                    'description' => 'OK',
                    'content' => ['application/json' => ['schema' => [
                        '$ref' => '#/components/schemas/' . $this->getResponseObjectNameForType($type),
                    ],],],
                ],
            ],
        ];

        $this->checkPermissionsAndAddSecurity($type, $definition, 'create');
        return [
            'post' => $definition
        ];
    }

    #[ArrayShape(['post' => "array"])] private function generateLoginPath(Role $role): array
    {
        $definition = [
            'tags' => [$role->slug],
            'operationId' => Str::slug('login ' . $role->slug),
            'summary' => 'Login with the ' . $role->name . ' role',
            'requestBody' => ['content' => ['application/json' => ['schema' => [
                '$ref' => '#/components/schemas/login',
            ],],],],
            'responses' => [
                '200' => [
                    'description' => 'OK',
                    'content' => ['application/json' => (object)[],],
                ],
            ],
        ];

        return [
            'post' => $definition
        ];
    }

    #[ArrayShape(['post' => "array"])] private function generateRegisterPath(Role $role): array
    {
        $definition = [
            'tags' => [$role->slug],
            'operationId' => Str::slug('register ' . $role->slug),
            'summary' => 'Register for the ' . $role->name . ' role',
            'requestBody' => ['content' => ['application/json' => ['schema' => [
                '$ref' => '#/components/schemas/register',
            ],],],],
            'responses' => [
                '200' => [
                    'description' => 'OK',
                    'content' => ['application/json' => (object)[],],
                ],
            ],
        ];

        return [
            'post' => $definition
        ];
    }

    private function generatePathForMethod(array &$paths, Type $type, string $method): bool
    {
        if ($this->checkIfMethodIsAllowed($type, Type::PERMISSIONS[$method])) {
            if ($method === 'index' || $method === 'store') {
                $route = route("api.okapi-instances.{$method}", $type, false);
            } else {
                $route = route("api.okapi-instances.{$method}", ['type' => $type, 'instance' => 'PLACEHOLDER'], false);
                $route = Str::replace('PLACEHOLDER', '{id}', $route);
            }
            $function = self::METHOD_TO_FUNCTION[$method];
            if (Arr::exists($paths, $route)) {
                $paths[$route] += $this->$function($type);
            } else {
                $paths[$route] = $this->$function($type);
            }

            return true;
        }

        return false;
    }

    #[ArrayShape(['openapi' => "string", 'info' => "array", 'servers' => "array[]", 'tags' => "array", 'paths' => "array", 'components' => "\string[][][]"])] public function generateDocumentation(): array
    {
        $documentation = [
            'openapi' => '3.0.0',
            'info' => [
                'title' => config('app.name'),
                'description' => 'This is a OpenAPI documentation generated by okAPI.
                You can find more about OpenAPI format at [https://swagger.io/docs/specification/about/](https://swagger.io/docs/specification/about/)
                You can find more about okAPI on [GitHub](https://github.com/smitoi/okapi).',
                'version' => '1.0.0',
            ],
            'servers' => [
                ['url' => config('app.url')],
            ],
            'tags' => [],
            'paths' => [],
            'components' => [
                'securitySchemes' => [
                    'bearerAuth' => [
                        'type' => 'http',
                        'scheme' => 'bearer',
                    ],
                    'apiKeyAuth' => [
                        'type' => 'apiKey',
                        'in' => 'header',
                        'name' => 'x-api-key',
                    ],
                ],
                'schemas' => [
                    'login' => [
                        'type' => 'object',
                        'properties' => [
                            'email' => ['type' => 'string',],
                            'password' => ['type' => 'string',],
                        ],
                    ],
                    'register' => [
                        'type' => 'object',
                        'properties' => [
                            'name' => ['type' => 'string',],
                            'email' => ['type' => 'string',],
                            'password' => ['type' => 'string',],
                        ],
                    ]
                ]
            ],
        ];

        foreach (Type::query()->get() as $type) {
            $generated = false;
            foreach (array_keys(self::METHOD_TO_FUNCTION) as $method) {
                $generated = $this->generatePathForMethod($documentation['paths'], $type, $method) || $generated;
            }

            if ($generated) {
                $documentation['tags'][] = [
                    'name' => $type->slug,
                ];

                $documentation['components']['schemas'][$this->getRequestObjectNameForType($type)] = $this->generateTypeDefinition($type);
                $documentation['components']['schemas'][$this->getResponseObjectNameForType($type)] = $this->generateTypeDefinition($type, false);
            }
        }

        foreach (Role::query()->whereNotIn('name', [Role::ADMIN_ROLE, Role::PUBLIC_ROLE])->get() as $role) {
            if ($role->api_login) {
                $documentation['paths'][route('api.okapi-users.login', $role->slug, false)] = $this->generateLoginPath($role);
            }

            if ($role->api_register) {
                $documentation['paths'][route('api.okapi-users.register', $role->slug, false)] = $this->generateRegisterPath($role);
            }
        }

        return $documentation;
    }
}
