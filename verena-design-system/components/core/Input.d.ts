import React from 'react';
/** @startingPoint section="Components" subtitle="Underline text field, gold focus ring" viewport="700x100" */
export interface InputProps {
label?: string;
placeholder?: string;
type?: string;
value?: string;
onChange?: (e: React.ChangeEvent<HTMLInputElement>) => void;
}
export function Input(props: InputProps): JSX.Element;
