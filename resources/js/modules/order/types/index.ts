export interface Order {
    order_id: string;
    order_number: string;
    tenant_id: string;
    store_id: string;
    customer_id: string | null;
    customer_name: string | null;
    customer_phone: string | null;
    customer_email: string | null;
    status: string;
    order_type: string;
    fulfillment_status: string;
    payment_status: string;
    subtotal: number;
    shipping_total: number;
    tax_total: number;
    discount_total: number;
    grand_total: number;
    paid_total: number;
    refund_total: number;
    due_total: number;
    currency: string;
    shipping_address: OrderAddress;
    billing_address: OrderAddress | null;
    line_items: LineItem[];
    coupon_code: string | null;
    notes: string | null;
    payment_method: string | null;
    payment_reference: string | null;
    source: string;
    placed_at: string;
    metadata: Record<string, unknown> | null;
}

export interface OrderAddress {
    name: string;
    phone: string;
    address_line_1: string;
    address_line_2: string | null;
    city: string;
    state: string | null;
    postal_code: string;
    country: string;
    email: string | null;
}

export interface LineItem {
    product_id: string;
    variant_id: string | null;
    name: string;
    sku: string;
    quantity: number;
    unit_price: number;
    total_price: number;
    tax_amount: number | null;
    discount_amount: number | null;
    warehouse_id: string | null;
    barcode: string | null;
}

export interface Shipment {
    shipment_id: string;
    order_id: string;
    shipment_number: string;
    status: string;
    courier: string | null;
    courier_service: string | null;
    tracking_number: string | null;
    tracking_url: string | null;
    label_url: string | null;
    recipient_name: string | null;
    recipient_phone: string | null;
    recipient_address: string | null;
    recipient_city: string | null;
    recipient_postal_code: string | null;
    shipping_cost: number;
    cod_amount: number;
    declared_value: number;
    total_weight_grams: number | null;
    total_items: number;
    notes: string | null;
    items: ShipmentItem[];
    shipped_at: string | null;
    estimated_delivery: string | null;
    delivered_at: string | null;
    created_at: string;
}

export interface ShipmentItem {
    name: string;
    quantity: number;
    unit_price: number;
    product_id: string | null;
    variant_id: string | null;
    sku: string | null;
}

export interface OrderFilters {
    status?: string;
    order_type?: string;
    customer_id?: string;
    date_from?: string;
    date_to?: string;
    page?: number;
    per_page?: number;
}
