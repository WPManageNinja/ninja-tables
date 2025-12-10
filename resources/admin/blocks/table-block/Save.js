export default function Save({ attributes }) {
    const { tableId, dataSource } = attributes;

    if (!tableId) {
        return null;
    }

    return dataSource === 'drag_and_drop'
        ? `[ninja_table_builder id="${tableId}"]`
        : `[ninja_tables id="${tableId}"]`;
}
