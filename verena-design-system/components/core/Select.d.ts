import React from 'react';
/** @startingPoint section="Components" subtitle="Underline select field matching Input" viewport="700x100" */
export interface SelectProps {
label?: string;
options?: string[];
value?: string;
onChange?: (e: React.ChangeEvent<HTMLSelectElement>) => void;
}
export function Select(props: SelectProps): JSX.Element;
