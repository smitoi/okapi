<?php

namespace App\Services;

use App\Models\Okapi\Field;
use App\Models\Okapi\Type;
use App\Models\Role;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;

class DocumentationService
{
    protected const OUTPUT_DEFINITION_TYPES = [
        Field::TYPE_BOOLEAN => 'integer',
        Field::TYPE_FILE => 'string',
        Field::TYPE_ENUM => 'string',
        Field::TYPE_NUMBER => 'integer',
        Field::TYPE_STRING => 'string',
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

    private function generateTypeDefinition(Type $type, bool $request = true): array
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

            if ($field->type === Field::TYPE_ENUM) {
                $properties['enum'] = $field->properties->options;
            }

            if ($field->type === Field::TYPE_FILE && $request) {
                $properties = [
                    'type' => 'string',
                    'format' => 'binary',
                ];
            }

            $definition['properties'][$field->slug] = $properties;
        }

        foreach ($type->relationships as $relationship) {
            $definition['properties'][$relationship->slug] = [
                'type' => 'array',
                'items' => [
                    'type' => 'integer',
                ],
            ];
        }

        foreach ($type->reverse_relationships as $relationship) {
            $definition['properties'][$relationship->reverse_slug] = [
                'type' => 'array',
                'items' => [
                    'type' => 'integer',
                ],
            ];
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
        return $type->permissions()->where('name', 'like', "%.{$method}")->first();
    }

    private function checkIfMethodIsAllowed(Type $type, string $method): bool
    {
        $permission = $this->getPermissionForTypeAndMethod($type, $method);
        return $permission?->roles()->count();
    }

    private function checkPermissionsAndAddSecurity(Type $type, array &$definition, string $method): void
    {
        $permission = $this->getPermissionForTypeAndMethod($type, $method);
        /** @var Permission $permission */
        if ($permission && $permission->roles()->where('name', Role::PUBLIC_ROLE)->doesntExist()) {
            $definition['security'] = [[
                'bearerAuth' => [],
            ],];

            $definition['responses']['403'] = [
                'description' => 'Action not allowed',
            ];
        }
    }

    private function generateListPath(Type $type): array
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

    private function generateViewPath(Type $type): array
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

        $this->checkPermissionsAndAddSecurity($type, $definition, 'create');
        return [
            'get' => $definition
        ];
    }

    private function generateEditPath(Type $type): array
    {
        $definition = [
            'tags' => [$type->slug],
            'operationId' => Str::slug('edit ' . $type->slug),
            'summary' => 'Update a ' . $type->name . ' instance',
            'requestBody' => ['content' => ['multipart/form-data' => ['schema' => [
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

        $this->checkPermissionsAndAddSecurity($type, $definition, 'create');
        return [
            'patch' => $definition
        ];
    }

    private function generateDeletePath(Type $type): array
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

        $this->checkPermissionsAndAddSecurity($type, $definition, 'create');
        return [
            'delete' => $definition
        ];
    }

    private function generateCreatePath(Type $type): array
    {
        $definition = [
            'tags' => [$type->slug],
            'operationId' => Str::slug('create ' . $type->slug),
            'summary' => 'Add a new ' . $type->name . ' instance',
            'requestBody' => ['content' => ['multipart/form-data' => ['schema' => [
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

    public function generateDocumentation(): array
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
                ],
            ]

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

        return $documentation;
    }
}
