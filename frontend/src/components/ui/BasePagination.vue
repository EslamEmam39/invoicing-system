<script setup lang="ts">
withDefaults(defineProps<{ page: number; lastPage: number; total: number; perPage?: number }>(), { perPage: 15 })
defineEmits<{ change: [page: number]; 'per-page-change': [perPage: number] }>()
</script>
<template>
  <nav v-if="lastPage > 1" class="pagination" aria-label="Pagination">
    <button class="pagination__button" type="button" :disabled="page === 1" @click="$emit('change', page - 1)">
      <span aria-hidden="true">←</span>
      Prev
    </button>
    <div class="pagination__controls"><label>Page<select :value="page" @change="$emit('change', Number(($event.target as HTMLSelectElement).value))"><option v-for="number in lastPage" :key="number" :value="number">{{ number }}</option></select></label><label>Rows<select :value="perPage" @change="$emit('per-page-change', Number(($event.target as HTMLSelectElement).value))"><option v-for="number in [15, 25, 50, 100]" :key="number" :value="number">{{ number }}</option></select></label><span>{{ total }} results</span></div>
    <button class="pagination__button" type="button" :disabled="page === lastPage" @click="$emit('change', page + 1)">
      Next
      <span aria-hidden="true">→</span>
    </button>
  </nav>
</template>

<style scoped>
.pagination {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  margin-top: 18px;
  padding: 12px 14px;
  border: 1px solid #e6ebe6;
  border-radius: 12px;
  background: #fff;
}

.pagination__button {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  min-width: 74px;
  padding: 7px 10px;
  border: 1px solid #d7e2db;
  border-radius: 8px;
  color: #216b54;
  background: #fff;
  font-size: 0.84rem;
  font-weight: 700;
}

.pagination__button:hover:not(:disabled) {
  border-color: #216b54;
  background: #edf6ef;
}

.pagination__button:disabled {
  cursor: not-allowed;
  color: #a5afa9;
  border-color: #edf0ed;
  background: #fafbf9;
}

.pagination__controls { display: flex; align-items: center; gap: 10px; color: #708079; font-size: .78rem; }
.pagination__controls label { display: flex; align-items: center; gap: 5px; font-size: .78rem; }
.pagination__controls select { width: auto; padding: 5px 24px 5px 7px; border-radius: 6px; font-size: .78rem; }

@media (max-width: 480px) {
  .pagination { gap: 8px; padding: 10px; }
  .pagination__button { min-width: auto; padding: 9px; font-size: 0; }
  .pagination__button span { font-size: 1rem; }
}
</style>
