import React from 'react';
/** @startingPoint section="Components" subtitle="Product tile with placeholder image, wishlist hover, badge" viewport="700x340" */
export interface ProductCardProps {
name: string;
price: string;
badge?: string;
onAdd?: () => void;
}
export function ProductCard(props: ProductCardProps): JSX.Element;
