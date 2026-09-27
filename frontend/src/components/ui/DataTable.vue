<script setup lang="ts">
export type TableColumn = { key: string; label: string }
defineProps<{ columns: TableColumn[]; rows: Array<Record<string, any>>; rowKey: string; emptyMessage?: string }>()
</script>
<template>
    <table>
        <thead>
            <tr>
                <th v-for="column in columns" :key="column.key">{{ column.label }}</th>
            </tr>
        </thead>
        <tbody>
            <tr v-for="row in rows" :key="String(row[rowKey])">
                <td v-for="column in columns" :key="column.key">
                    <slot :name="`cell-${column.key}`" :row="row">{{ row[column.key] }}</slot>
                </td>
            </tr>
            <tr v-if="!rows.length">
                <td :colspan="columns.length" class="empty">{{ emptyMessage ?? 'No records found.' }}</td>
            </tr>
        </tbody>
    </table>
</template>
