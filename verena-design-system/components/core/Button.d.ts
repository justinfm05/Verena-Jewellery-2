import React from 'react';
/** @startingPoint section="Components" subtitle="Pill buttons in four variants and three sizes" viewport="700x220" */
export interface ButtonProps {
variant?: 'primary' | 'secondary' | 'ghost' | 'accent';
size?: 's' | 'm' | 'l';
disabled?: boolean;
onClick?: () => void;
children?: React.ReactNode;
style?: React.CSSProperties;
}
export function Button(props: ButtonProps): JSX.Element;
