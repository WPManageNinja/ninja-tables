<template>
	<div class="tablenav-pages">
		<span v-if="paginate.total" class="displaying-num">{{ paginate.total }} {{ $t('items') }}</span>

		<span class="pagination-links">
            <template v-if="paginate.current_page == 1">
                <span class="tablenav-pages-navspan" aria-hidden="true">«</span>
                <span class="tablenav-pages-navspan" aria-hidden="true">‹</span>
            </template>
            <template v-else>
                 <a class="first-page" href="#" @click.prevent="goToPage(1)"><span class="screen-reader-text">{{ $t('First page') }}</span><span aria-hidden="true">«</span></a>
                <a class="prev-page" href="#" @click.prevent="goToPage(paginate.current_page - 1)"><span class="screen-reader-text">{{ $t('Previous page')}}</span><span aria-hidden="true">‹</span></a>
            </template>

            <span class="screen-reader-text">{{ $t('Current Page') }}</span>
            <input @keydown.enter.prevent="goToPage(pageNumberInput)" class="current-page" id="current-page-selector" v-model="pageNumberInput" type="text" size="2" aria-describedby="table-paging">
            {{ $t('of') }}
            <span class="total-pages">{{ paginate.last_page }}</span>

            <template v-if="paginate.current_page == paginate.last_page">
                <span class="tablenav-pages-navspan" aria-hidden="true">›</span>
                <span class="tablenav-pages-navspan" aria-hidden="true">»</span>
            </template>
            <template v-else>
                <a class="next-page" href="#" @click.prevent="goToPage(paginate.current_page + 1)"><span class="screen-reader-text">{{ $t('Next page') }}</span><span aria-hidden="true">›</span></a>
                <a class="last-page" href="#" @click.prevent="goToPage(paginate.last_page)"><span class="screen-reader-text">{{ $t('Last page')}}</span><span aria-hidden="true">»</span></a>
            </template>
        </span>
	</div>
</template>

<script type="text/babel">
	export default {
	    name: 'NinjaPagination',
		props: ['paginate'],
		data() {
	        return {
                pageNumberInput: 1
	        }
		},
		methods: {
            goToPage(pageNumber) {
                // verify the page number
                if(pageNumber >= 1 && pageNumber <= this.paginate.last_page) {
                    this.$emit('change_page', pageNumber);
                    this.pageNumberInput = pageNumber;
                } else {
                    alert('invalid page number');
                }
			}
		}
	}
</script>
