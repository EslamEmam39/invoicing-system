export const money = (value: string | number) =>
    new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(Number(value))
export const date = (value: string) =>
    new Intl.DateTimeFormat('en-GB', { dateStyle: 'medium' }).format(new Date(value))
