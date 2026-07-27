import React from 'react';
/** @startingPoint section="Components" subtitle="Small uppercase pill label for tags and status" viewport="700x120" */
export interface BadgeProps {
variant?: 'neutral' | 'accent' | 'inverse';
children?: React.ReactNode;
}
export function Badge(props: BadgeProps): JSX.Element;
