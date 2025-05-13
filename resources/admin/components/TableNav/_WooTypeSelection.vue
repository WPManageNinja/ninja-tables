<template>
    <div>
        <div class="nt_query_header">
            <h3 class="nt-modal-subtitle">{{term.title}}</h3>
            <p class="nt-modal-description">{{term.description}}</p>
        </div>

        <div class="nt-checkbox-group-wrapper mt-[10px]">
            <div class="nt-checkbox-group-header"
                 style="border-bottom: 1px solid #E1E4EA">
                <div>{{ $t('Select') }}</div>
                <div>
                    <el-checkbox
                        v-model="checkAll"
                        :indeterminate="isIndeterminate"
                        @change="handleCheckAllChange"
                    >
                       {{ $t('Select all') }}
                    </el-checkbox>
                </div>
            </div>
            <div class="p-4">
                <el-checkbox-group
                    v-model="localSelections"
                    @change="handleSelectionChange"
                >
                    <el-checkbox
                        v-for="taxonomy in term.terms"
                        :key="taxonomy.slug"
                        :value="taxonomy.slug"
                    >
                        {{taxonomy.name}} ({{taxonomy.count}})
                    </el-checkbox>
                </el-checkbox-group>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: "_WooTypeSelection",
    props: ['term', 'termName', 'querySelections'],
    data() {
        return {
            localSelections: [],
            checkAll: false,
            isIndeterminate: false
        }
    },
    watch: {
        querySelections: {
            immediate: true,
            handler(newVal) {
                this.localSelections = Array.isArray(newVal) ? [...newVal] : [];
                this.updateCheckAllState();
            }
        }
    },
    methods: {
        handleSelectionChange() {
            this.updateCheckAllState();
            this.$emit('selectionChange', this.localSelections);
        },
        handleCheckAllChange(val) {
            this.localSelections = val ? this.term.terms.map(taxonomy => taxonomy.slug) : [];
            this.$emit('selectionChange', this.localSelections);
        },
        updateCheckAllState() {
            if (!this.term || !this.term.terms) return;
            
            const termsCount = this.term.terms.length;
            const selectedCount = this.localSelections.length;
            
            this.checkAll = termsCount > 0 && selectedCount === termsCount;
            this.isIndeterminate = selectedCount > 0 && selectedCount < termsCount;
        }
    }
}
</script>
