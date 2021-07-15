interface Timestampable {
    readonly created_at: string;
    readonly deleted_at: string | null;
    readonly updated_at: string;
}

export interface Category extends Timestampable {
    readonly id: string;
    name: string;
    description: string;
    is_active: boolean;
}

export interface Genre extends Timestampable {
    readonly id: string;
    name: string;
    is_active: number;
    categories: Category[];
}