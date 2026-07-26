<?php

use BradieTilley\Builder\PhpArgument;
use BradieTilley\Builder\PhpAttribute;
use BradieTilley\Builder\PhpClass;
use BradieTilley\Builder\PhpClassConstant;
use BradieTilley\Builder\PhpEnum;
use BradieTilley\Builder\PhpEnumCase;
use BradieTilley\Builder\PhpInterface;
use BradieTilley\Builder\PhpMethod;
use BradieTilley\Builder\PhpProperty;
use BradieTilley\Builder\PhpPropertyGetHook;
use BradieTilley\Builder\PhpPropertySetHook;
use BradieTilley\Builder\PhpTemplate;
use BradieTilley\Builder\PhpTrait;
use BradieTilley\Builder\PhpTraitAlias;
use BradieTilley\Builder\PhpTraitInsteadof;
use BradieTilley\Builder\PhpUseTrait;
use BradieTilley\Builder\Types\PhpArrayType;
use BradieTilley\Builder\Types\PhpCallableType;
use BradieTilley\Builder\Types\PhpIntersectionType;
use BradieTilley\Builder\Types\PhpNamedType;
use BradieTilley\Builder\Types\PhpUnionType;

function kitchenSinkClass(): PhpClass
{
    $class = new PhpClass(
        namespace: 'App\\Models',
        name: 'KitchenSink',
        extends: 'App\\Models\\Model',
        implements: [
            'App\\Contracts\\Identifiable',
            'App\\Contracts\\Sluggable',
        ],
        traits: [
            'App\\Models\\Concerns\\HasTimestamps',
            new PhpUseTrait(
                name: ['App\\Models\\Concerns\\HasSlug', 'App\\Models\\Concerns\\HasUuid'],
                aliases: [
                    new PhpTraitAlias(
                        method: 'bootHasSlug',
                        alias: 'bootSlug',
                        visibility: PhpTraitAlias::VISIBILITY_PROTECTED,
                        trait: 'App\\Models\\Concerns\\HasSlug',
                    ),
                ],
                insteadof: [
                    new PhpTraitInsteadof(
                        method: 'boot',
                        from: 'App\\Models\\Concerns\\HasSlug',
                        insteadOf: 'App\\Models\\Concerns\\HasUuid',
                    ),
                ],
            ),
        ],
        constants: [
            new PhpClassConstant(
                name: 'TYPE',
                value: "'kitchen'",
                type: 'string',
                attributes: [
                    new PhpAttribute('Deprecated', ["message: 'use NAME'"]),
                ],
            ),
            new PhpClassConstant(
                name: 'MAX',
                value: '100',
                visibility: PhpClassConstant::VISIBILITY_PROTECTED,
                final: true,
                type: 'int',
            ),
        ],
        attributes: [
            new PhpAttribute('AllowDynamicProperties'),
            new PhpAttribute('App\\Attributes\\OwnedBy', ["team: 'platform'"]),
        ],
        properties: [
            new PhpProperty(
                type: 'string',
                name: 'title',
                description: 'Display title',
                defaultValue: "''",
                attributes: [
                    new PhpAttribute('App\\Attributes\\Column', ["length: 255"]),
                ],
            ),
            new PhpProperty(
                type: new PhpUnionType(['string', 'int']),
                name: 'code',
                visibility: PhpProperty::VISIBILITY_PROTECTED,
                setVisibility: PhpProperty::VISIBILITY_PRIVATE,
            ),
            new PhpProperty(
                type: new PhpArrayType(value: 'Illuminate\\Support\\Collection', key: 'string'),
                name: 'meta',
                static: true,
            ),
            new PhpProperty(
                type: 'string',
                name: 'slug',
                get: new PhpPropertyGetHook(
                    byRef: true,
                    expression: '$this->slug',
                ),
                set: new PhpPropertySetHook(
                    type: 'string',
                    expression: '$this->slug = strtolower($value)',
                ),
            ),
            new PhpProperty(
                type: 'string',
                name: 'label',
                final: true,
                get: new PhpPropertyGetHook(lines: ['return strtoupper($this->title);']),
                set: new PhpPropertySetHook(
                    type: 'string',
                    name: 'incoming',
                    lines: ['$this->title = $incoming;'],
                ),
            ),
        ],
        methods: [
            new PhpMethod(
                name: '__construct',
                args: [
                    new PhpArgument(
                        visibility: PhpArgument::VISIBILITY_PUBLIC,
                        readonly: true,
                        type: 'string',
                        name: 'id',
                    ),
                    new PhpArgument(
                        type: 'int',
                        name: 'count',
                        byRef: true,
                    ),
                    new PhpArgument(
                        visibility: PhpArgument::VISIBILITY_PROTECTED,
                        type: 'string',
                        name: 'nickname',
                        get: new PhpPropertyGetHook(lines: ['return $this->nickname;']),
                        set: new PhpPropertySetHook(
                            type: 'string',
                            lines: ['$this->nickname = trim($value);'],
                        ),
                    ),
                    new PhpArgument(
                        visibility: PhpArgument::VISIBILITY_PUBLIC,
                        setVisibility: PhpArgument::VISIBILITY_PRIVATE,
                        final: true,
                        type: 'string',
                        name: 'name',
                        defaultValue: "'untitled'",
                    ),
                    new PhpArgument(
                        type: new PhpArrayType(value: 'string'),
                        name: 'flags',
                        defaultValue: '[]',
                        description: 'Optional feature flags',
                    ),
                    new PhpArgument(
                        type: 'string',
                        name: 'tags',
                        variadic: true,
                    ),
                ],
                lines: [
                    '$this->title = $name;',
                    '',
                    '// ready',
                ],
            ),
            new PhpMethod(
                name: 'map',
                static: true,
                templates: [
                    new PhpTemplate(name: 'TReturn', of: 'mixed'),
                ],
                args: [
                    new PhpArgument(
                        type: new PhpCallableType(
                            parameters: ['mixed'],
                            return: 'TReturn',
                        ),
                        name: 'callback',
                    ),
                ],
                return: new PhpArrayType(value: 'TReturn'),
                lines: ['return [];'],
                description: 'Map values with a callback',
            ),
            new PhpMethod(
                name: 'asEloquent',
                final: true,
                return: 'Illuminate\\Database\\Eloquent\\Model',
                lines: ['return $this;'],
                throws: [
                    'RuntimeException',
                    'Illuminate\\Database\\Eloquent\\ModelNotFoundException',
                ],
                attributes: [
                    new PhpAttribute('App\\Attributes\\Internal'),
                ],
            ),
            new PhpMethod(
                name: 'resolve',
                returnsReference: true,
                args: [
                    new PhpArgument(
                        type: new PhpIntersectionType([
                            'App\\Contracts\\Identifiable',
                            'App\\Contracts\\Sluggable',
                        ]),
                        name: 'entity',
                    ),
                    new PhpArgument(
                        type: new PhpNamedType('Closure', nullable: true),
                        name: 'fallback',
                        defaultValue: 'null',
                    ),
                ],
                return: new PhpUnionType([
                    'App\\Contracts\\Identifiable',
                    'null',
                ]),
                lines: ['return $entity;'],
            ),
            new PhpMethod(
                name: 'transform',
                visibility: PhpMethod::VISIBILITY_PROTECTED,
                args: [
                    new PhpArgument(
                        type: new PhpCallableType(
                            parameters: ['string'],
                            return: 'bool',
                            useClosure: true,
                            nullable: true,
                        ),
                        name: 'filter',
                        defaultValue: 'null',
                        attributes: [
                            new PhpAttribute('SensitiveParameter'),
                        ],
                    ),
                ],
                return: 'integer',
                lines: ['return 0;'],
            ),
            new PhpMethod(
                name: 'boot',
                visibility: PhpMethod::VISIBILITY_PROTECTED,
                abstract: true,
                return: 'void',
            ),
        ],
        description: 'Kitchen-sink class exercising the builder surface',
        templates: [
            new PhpTemplate(name: 'TKey', of: 'array-key', covariant: true),
            new PhpTemplate(name: 'TValue', contravariant: true),
            'TExtra',
        ],
        abstract: true,
    );

    $tagQuery = $class->import('App\\Support\\TagQuery');
    $class->methods[] = new PhpMethod(
        name: 'syncTags',
        args: [
            new PhpArgument(type: new PhpArrayType(value: 'string'), name: 'tags'),
        ],
        return: 'static',
        lines: [
            "\$query = {$tagQuery}::make(\$tags);",
            'return $this;',
        ],
    );

    return $class;
}

function kitchenSinkInterface(): PhpInterface
{
    return new PhpInterface(
        namespace: 'App\\Contracts',
        name: 'KitchenContract',
        extends: [
            'App\\Contracts\\Identifiable',
            'App\\Contracts\\Serializable',
        ],
        constants: [
            new PhpClassConstant(
                name: 'VERSION',
                value: '1',
                type: 'int',
                final: true,
            ),
        ],
        properties: [
            new PhpProperty(
                type: 'string',
                name: 'name',
                get: new PhpPropertyGetHook(stub: true),
                set: new PhpPropertySetHook(stub: true),
            ),
            new PhpProperty(
                type: new PhpArrayType(value: 'App\\Enums\\Status'),
                name: 'statuses',
                get: new PhpPropertyGetHook(stub: true),
            ),
        ],
        methods: [
            new PhpMethod(
                name: 'handle',
                args: [
                    new PhpArgument(
                        type: new PhpUnionType(['string', 'int']),
                        name: 'input',
                        description: 'Raw input',
                    ),
                    new PhpArgument(
                        type: new PhpCallableType(parameters: ['mixed'], return: 'void'),
                        name: 'next',
                    ),
                ],
                return: new PhpIntersectionType([
                    'App\\Contracts\\Identifiable',
                    'Stringable',
                ]),
                throws: ['InvalidArgumentException'],
                templates: ['TContext'],
                description: 'Handle the contract payload',
                lines: ['return $this;'],
            ),
            new PhpMethod(
                name: 'id',
                return: 'string',
                lines: ['return "";'],
            ),
        ],
        attributes: [
            new PhpAttribute('App\\Attributes\\Contract'),
        ],
        description: 'Kitchen-sink interface',
        templates: [
            new PhpTemplate(name: 'TResource', of: 'object'),
        ],
    );
}

function kitchenSinkEnum(): PhpEnum
{
    return new PhpEnum(
        namespace: 'App\\Enums',
        name: 'KitchenStatus',
        backedType: new PhpNamedType('string'),
        implements: [
            'App\\Contracts\\Labelled',
            'JsonSerializable',
        ],
        cases: [
            new PhpEnumCase(
                name: 'Draft',
                value: "'draft'",
                attributes: [
                    new PhpAttribute('App\\Attributes\\Colour', ["value: 'grey'"]),
                ],
            ),
            new PhpEnumCase(name: 'Published', value: "'published'"),
            new PhpEnumCase(name: 'Archived', value: "'archived'"),
        ],
        constants: [
            new PhpClassConstant(
                name: 'DEFAULT',
                value: "self::Draft",
                type: 'self',
                visibility: PhpClassConstant::VISIBILITY_PRIVATE,
            ),
        ],
        methods: [
            new PhpMethod(
                name: 'label',
                return: 'string',
                lines: [
                    'return match ($this) {',
                    "    self::Draft => 'Draft',",
                    "    self::Published => 'Published',",
                    "    self::Archived => 'Archived',",
                    '};',
                ],
            ),
            new PhpMethod(
                name: 'fromModel',
                static: true,
                args: [
                    new PhpArgument(
                        type: 'Illuminate\\Database\\Eloquent\\Model',
                        name: 'model',
                    ),
                ],
                return: 'self',
                lines: ['return self::from((string) $model->status);'],
                throws: ['ValueError'],
            ),
            new PhpMethod(
                name: 'jsonSerialize',
                return: 'array',
                lines: ['return ["value" => $this->value];'],
            ),
        ],
        attributes: [
            new PhpAttribute('App\\Attributes\\EnumMeta', ["group: 'content'"]),
        ],
        description: 'Kitchen-sink backed enum',
        templates: [
            new PhpTemplate(name: 'TMeta'),
        ],
    );
}

function kitchenSinkPureEnum(): PhpEnum
{
    return new PhpEnum(
        namespace: 'App\\Enums',
        name: 'KitchenRole',
        cases: [
            new PhpEnumCase(name: 'Admin'),
            new PhpEnumCase(name: 'Editor'),
            new PhpEnumCase(name: 'Viewer'),
        ],
        methods: [
            new PhpMethod(
                name: 'isStaff',
                return: 'bool',
                lines: ['return $this === self::Admin || $this === self::Editor;'],
            ),
        ],
        description: 'Kitchen-sink pure enum',
        strictTypes: false,
    );
}

function kitchenSinkTrait(): PhpTrait
{
    return new PhpTrait(
        namespace: 'App\\Concerns',
        name: 'KitchenTrait',
        traits: [
            new PhpUseTrait(
                name: ['App\\Concerns\\LogsActivity', 'App\\Concerns\\TracksChanges'],
                aliases: [
                    'log' => 'writeLog',
                ],
                insteadof: [
                    'track' => 'App\\Concerns\\TracksChanges',
                ],
            ),
            'App\\Concerns\\HasHelpers',
        ],
        constants: [
            new PhpClassConstant(
                name: 'PREFIX',
                value: "'kt_'",
                type: 'string',
                visibility: PhpClassConstant::VISIBILITY_PROTECTED,
            ),
        ],
        properties: [
            new PhpProperty(
                type: 'bool',
                name: 'enabled',
                defaultValue: 'true',
            ),
            new PhpProperty(
                type: new PhpArrayType(value: 'string'),
                name: 'buffer',
                visibility: PhpProperty::VISIBILITY_PRIVATE,
            ),
            new PhpProperty(
                abstract: true,
                type: 'string',
                name: 'key',
                get: new PhpPropertyGetHook(stub: true),
            ),
        ],
        methods: [
            new PhpMethod(
                name: 'bootKitchenTrait',
                visibility: PhpMethod::VISIBILITY_PROTECTED,
                lines: ['$this->enabled = true;'],
            ),
            new PhpMethod(
                name: 'flush',
                return: new PhpArrayType(value: 'string'),
                lines: [
                    '$copy = $this->buffer;',
                    '$this->buffer = [];',
                    '',
                    'return $copy;',
                ],
                description: 'Flush the buffer',
            ),
            new PhpMethod(
                name: 'configure',
                args: [
                    new PhpArgument(
                        type: new PhpCallableType(
                            parameters: ['self'],
                            return: 'void',
                        ),
                        name: 'callback',
                    ),
                ],
                return: 'static',
                lines: [
                    '$callback($this);',
                    '',
                    'return $this;',
                ],
            ),
        ],
        attributes: [
            new PhpAttribute('App\\Attributes\\TraitMarker'),
        ],
        description: 'Kitchen-sink trait',
        templates: [
            'TOwner',
        ],
    );
}
