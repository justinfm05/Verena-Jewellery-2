import React from 'react';
/** @startingPoint section="Components" subtitle="Circular icon-only button, light or inverse" viewport="700x140" */
export interface IconButtonProps {
icon: string;
label: string;
variant?: 'ghost' | 'inverse';
onClick?: () => void;
style?: React.CSSProperties;
}
export function IconButton(props: IconButtonProps): JSX.Element;
