export const euFormat = (value) => {
    value = value.toString();

    const split = value.split('.')[1];

    const minimumFractionDigits = split ? split.length : 0;

    return  Number(value).toLocaleString("es-ES", {minimumFractionDigits});
}
