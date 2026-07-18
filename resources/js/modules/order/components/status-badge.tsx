type StatusBadgeProps = {
    status: string;
    size?: 'sm' | 'md';
};

const statusColors: Record<string, string> = {
    pending: 'bg-yellow-100 text-yellow-800',
    confirmed: 'bg-blue-100 text-blue-800',
    processing: 'bg-indigo-100 text-indigo-800',
    ready_for_pickup: 'bg-purple-100 text-purple-800',
    out_for_delivery: 'bg-orange-100 text-orange-800',
    partially_shipped: 'bg-cyan-100 text-cyan-800',
    shipped: 'bg-cyan-100 text-cyan-800',
    delivered: 'bg-green-100 text-green-800',
    completed: 'bg-green-100 text-green-800',
    cancelled: 'bg-red-100 text-red-800',
    refunded: 'bg-gray-100 text-gray-800',
    partially_refunded: 'bg-gray-100 text-gray-800',
    on_hold: 'bg-slate-100 text-slate-800',
    failed: 'bg-red-100 text-red-800',
    label_created: 'bg-blue-100 text-blue-800',
    picked_up: 'bg-indigo-100 text-indigo-800',
    in_transit: 'bg-purple-100 text-purple-800',
    out_for_delivery: 'bg-orange-100 text-orange-800',
    delivery_failed: 'bg-red-100 text-red-800',
    returned_to_sender: 'bg-gray-100 text-gray-800',
};

export function StatusBadge({ status, size = 'md' }: StatusBadgeProps) {
    const sizeClass = size === 'sm' ? 'px-2 py-0.5 text-xs' : 'px-2.5 py-1 text-sm';

    return (
        <span className={`inline-flex rounded-full font-medium ${sizeClass} ${statusColors[status] || 'bg-gray-100 text-gray-800'}`}>
            {status.replace(/_/g, ' ')}
        </span>
    );
}
