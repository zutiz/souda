export type SkuStrategy = 'pattern' | 'sequential' | 'attribute' | 'custom';

export type SkuPatternConfig = {
    strategy: 'pattern';
    prefix?: string;
    separator: string;
    attributeOrder: string[];
    includeProductSku: boolean;
};

export type SkuSequentialConfig = {
    strategy: 'sequential';
    prefix: string;
    padding: number;
    startFrom: number;
};

export type SkuAttributeConfig = {
    strategy: 'attribute';
    separator: string;
    attributeOrder: string[];
    maxLength: number;
};

export type SkuCustomConfig = {
    strategy: 'custom';
    template: string;
};

export type SkuGenerationConfig =
    | SkuPatternConfig
    | SkuSequentialConfig
    | SkuAttributeConfig
    | SkuCustomConfig;

export type BarcodeFormat = 'ean13' | 'upc' | 'code128';

export type BarcodeGenerationConfig = {
    format: BarcodeFormat;
    prefix?: string;
    variantIndexPadding?: number;
};
