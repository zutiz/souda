import { Head, useForm, usePage } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { PageHeader } from '@/modules/shared/components/page-header';
import { Button } from '@/components/ui/button';
import type { BreadcrumbItem } from '@/types';
import { type FormEventHandler } from 'react';

type ConditionType = {
    value: string;
    label: string;
    defaultConfig: Record<string, unknown>;
};

type ActionType = {
    value: string;
    label: string;
};

type CreateRulePageProps = {
    conditionTypes: ConditionType[];
    actionTypes: ActionType[];
};

export default function RulesCreate() {
    const { conditionTypes, actionTypes } = usePage<CreateRulePageProps>().props;

    const { data, setData, post, processing, errors } = useForm({
        name: '',
        description: '',
        condition_type: 'low_stock',
        condition_config: { threshold: 10 },
        action_type: 'create_alert',
        action_config: { severity: 'warning' },
        schedule: 'every_fifteen_minutes',
    });

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Inventory', href: '/inventory' },
        { title: 'Rules', href: '/inventory/rules' },
        { title: 'Create', href: '/inventory/rules/create' },
    ];

    function handleConditionChange(value: string) {
        const ct = conditionTypes.find((c) => c.value === value);
        setData({
            ...data,
            condition_type: value,
            condition_config: ct?.defaultConfig ?? {},
        });
    }

    function updateConditionConfig(key: string, value: number | string) {
        setData({
            ...data,
            condition_config: { ...data.condition_config, [key]: value },
        });
    }

    function updateActionConfig(key: string, value: string) {
        setData({
            ...data,
            action_config: { ...data.action_config, [key]: value },
        });
    }

    const handleSubmit: FormEventHandler = (e) => {
        e.preventDefault();
        post('/inventory/rules');
    };

    const selectedCondition = conditionTypes.find((c) => c.value === data.condition_type);

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Create Rule" />

            <PageHeader title="Create Rule" description="Define an IF-THEN automation rule" />

            <div className="max-w-2xl">
                <form onSubmit={handleSubmit} className="space-y-6">
                    <div>
                        <label className="block text-sm font-medium mb-1">Name</label>
                        <input
                            type="text"
                            value={data.name}
                            onChange={(e) => setData('name', e.target.value)}
                            className="w-full rounded-lg border border-sidebar-border px-3 py-2 text-sm"
                            placeholder="e.g. Dead Stock Detection"
                        />
                        {errors.name && <p className="text-red-500 text-xs mt-1">{errors.name}</p>}
                    </div>

                    <div>
                        <label className="block text-sm font-medium mb-1">Description</label>
                        <textarea
                            value={data.description}
                            onChange={(e) => setData('description', e.target.value)}
                            className="w-full rounded-lg border border-sidebar-border px-3 py-2 text-sm"
                            rows={3}
                            placeholder="Optional description of what this rule does"
                        />
                        {errors.description && <p className="text-red-500 text-xs mt-1">{errors.description}</p>}
                    </div>

                    <div>
                        <label className="block text-sm font-medium mb-1">Condition (IF)</label>
                        <select
                            value={data.condition_type}
                            onChange={(e) => handleConditionChange(e.target.value)}
                            className="w-full rounded-lg border border-sidebar-border px-3 py-2 text-sm"
                        >
                            {conditionTypes.map((ct) => (
                                <option key={ct.value} value={ct.value}>{ct.label}</option>
                            ))}
                        </select>
                        {errors.condition_type && <p className="text-red-500 text-xs mt-1">{errors.condition_type}</p>}
                    </div>

                    {selectedCondition && (
                        <div className="rounded-lg border border-sidebar-border p-4 space-y-3">
                            <h3 className="text-sm font-medium">Condition Configuration</h3>
                            {Object.entries(selectedCondition.defaultConfig).map(([key, defaultValue]) => (
                                <div key={key}>
                                    <label className="block text-xs font-medium mb-1 capitalize">
                                        {key.replace(/_/g, ' ')}
                                    </label>
                                    <input
                                        type={typeof defaultValue === 'number' ? 'number' : 'text'}
                                        step={typeof defaultValue === 'number' ? 'any' : undefined}
                                        value={String(data.condition_config[key] ?? defaultValue)}
                                        onChange={(e) => updateConditionConfig(key, e.target.valueAsNumber ?? e.target.value)}
                                        className="w-full rounded-lg border border-sidebar-border px-3 py-2 text-sm"
                                    />
                                </div>
                            ))}
                        </div>
                    )}

                    <div>
                        <label className="block text-sm font-medium mb-1">Action (THEN)</label>
                        <select
                            value={data.action_type}
                            onChange={(e) => setData('action_type', e.target.value)}
                            className="w-full rounded-lg border border-sidebar-border px-3 py-2 text-sm"
                        >
                            {actionTypes.map((at) => (
                                <option key={at.value} value={at.value}>{at.label}</option>
                            ))}
                        </select>
                        {errors.action_type && <p className="text-red-500 text-xs mt-1">{errors.action_type}</p>}
                    </div>

                    <div>
                        <label className="block text-sm font-medium mb-1">Severity</label>
                        <select
                            value={data.action_config.severity ?? 'warning'}
                            onChange={(e) => updateActionConfig('severity', e.target.value)}
                            className="w-full rounded-lg border border-sidebar-border px-3 py-2 text-sm"
                        >
                            <option value="info">Info</option>
                            <option value="warning">Warning</option>
                            <option value="critical">Critical</option>
                        </select>
                    </div>

                    <div>
                        <label className="block text-sm font-medium mb-1">Schedule</label>
                        <select
                            value={data.schedule}
                            onChange={(e) => setData('schedule', e.target.value)}
                            className="w-full rounded-lg border border-sidebar-border px-3 py-2 text-sm"
                        >
                            <option value="every_fifteen_minutes">Every 15 Minutes</option>
                            <option value="hourly">Hourly</option>
                            <option value="daily">Daily</option>
                        </select>
                        {errors.schedule && <p className="text-red-500 text-xs mt-1">{errors.schedule}</p>}
                    </div>

                    <div className="flex items-center gap-3">
                        <Button type="submit" disabled={processing}>Create Rule</Button>
                    </div>
                </form>
            </div>
        </AppLayout>
    );
}
