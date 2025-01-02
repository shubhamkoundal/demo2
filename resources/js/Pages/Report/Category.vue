<template>
  <admin-layout :title="$t('Category')">
    <div class="px-4 md:px-0">
      <tec-section-title class="-mx-4 md:mx-0 mb-6">
        <template #title>{{ $t('Category Report') }}</template>
        <template #description>{{ $t('Please review the data in the table below') }}</template>
      </tec-section-title>

      <div class="mb-6 flex justify-between items-center print:hidden">
        <search-filter v-model="form.search" class="w-full max-w-md mr-4" :close="close" @reset="reset">
          <!-- <label class="mt-4 block text-gray-700">{{ $t('Trashed') }}:</label> -->
          <auto-complete
            id="trashed"
            position="left"
            :label="$t('Trashed')"
            v-model="form.trashed"
            class="mt-1 w-full form-select"
            :suggestions="[
              { label: $t('Not Trashed'), value: null },
              { label: $t('With Trashed'), value: 'with' },
              { label: $t('Only Trashed'), value: 'only' },
            ]"
          >
          </auto-complete>
        </search-filter>
        <tec-dropdown align="right" width="48" v-if="$can(['create-items', 'import-items', 'export-items'])">
          <template #trigger>
            <button
              class="flex items-center px-4 py-3 bg-gray-800 rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 focus:outline-none focus:ring focus:ring-gray-300 transition ease-in-out duration-150"
            >
              <icons name="menu"></icons>
            </button>
          </template>

          <template #content>
            <tec-dropdown-link v-if="$can('create-items')" :href="route('items.create')">
              {{ $t('create_x', { x: $t('Item') }) }}
            </tec-dropdown-link>
            <a
              v-if="$can('export-items')"
              :href="route('items.export')"
              class="block px-4 py-2 leading-5 text-gray-700 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 transition duration-150 ease-in-out"
            >
              {{ $t('export_x', { x: $t('Items') }) }}
            </a>
            <tec-dropdown-link v-if="$can('import-items')" :href="route('items.import')">
              {{ $t('import_x', { x: $t('Items') }) }}
            </tec-dropdown-link>
          </template>
        </tec-dropdown>
      </div>
      <div id="dd-table" class="bg-white -mx-4 md:mx-0 md:rounded-md shadow overflow-y-visible overflow-x-auto">
        <table class="w-full overflow-y-visible">
          <tr class="text-left font-bold">
             <th class="px-6 pt-6 pb-4">{{ $t(' Category Name') }}</th>
            <th class="px-6 pt-6 pb-4">{{ $t('VDT Office') }}</th>
            <th class="px-6 pt-6 pb-4">{{ $t('DL Office') }}</th>
            <th class="px-6 pt-6 pb-4" >{{ $t('Nagrota Bagwan')}}</th>
             <th class="px-6 pt-6 pb-4" >{{ $t('Yol Bazar') }}</th>
               <th class="px-6 pt-6 pb-4" >{{ $t('Gaggal') }}</th>
                 <th class="px-6 pt-6 pb-4" >{{ $t('Yol Cantt') }}</th>
               <th class="pl-0 px-6 pt-6 pb-4" colspan="3">{{ $t('Total') }}</th>
          </tr>
          <tr :key="item.id" v-for="item in items.data" class="hover:bg-gray-100 focus-within:bg-gray-100">
            <td class="border-t" @click="goto(item)" :class="{ 'cursor-pointer': $can('read-items') }">
              <div class="px-6 py-4 w-30 whitespace-nowrap">
                {{ item.categories.map(c => c.name).join(', ') }} 
                <div>
                    {{ $t('Code') }}: <strong>{{ item.code }}</strong>
                  </div>
                <div v-if="item.has_variants == 1" class="flex items-center flex-wrap">
                  <span class="text-gray-600 mr-1">{{ $t('Variants') }}:</span>
                  <template v-for="v in item.variants" :key="v.code">
                    <template v-if="v.code">
                      <div class="ml-1">
                        <strong>{{ $t(v.code) }}:</strong>
                        {{
                          v.option
                            .filter(o => o)
                            .map(o => $t(o))
                            .join(', ')
                        }}
                      </div>
                    </template>
                  </template>
                </div>
              </div>
            </td>
             <td class="border-t" @click="goto(item)" v-if="$can('read-items')">
                    <div class="px-6 py-4 w-30 whitespace-nowrap">
                      <div v-for="(stock, index) in item.stock" :key="stock.id" class="w-full flex items-center justify-between">
                              <div v-if="index === 0">
                                <!-- Display the second quantity value here -->
                                {{ $number(stock.quantity) }} <span v-if="item.track_weight == 1">({{ $number(stock.weight) }})</span>
                              </div>
                            </div>
                        </div>
                  </td>
                 <td class="border-t" @click="goto(item)" v-if="$can('read-items')">
                    <div class="px-6 py-4 w-30 whitespace-nowrap">
                      <div v-for="(stock, index) in item.stock" :key="stock.id" class="w-full flex items-center justify-between">
                              <div v-if="index === 1">
                                <!-- Display the second quantity value here -->
                                {{ $number(stock.quantity) }} <span v-if="item.track_weight == 1">({{ $number(stock.weight) }})</span>
                              </div>
                            </div>
                        </div>
                  </td>
                  <!-- Add cells for Nagrota Bagwan, Yol Bazar, Gaggal, Yol Cantt, and Total warehouses -->
                   <td class="border-t" @click="goto(item)" v-if="$can('read-items')">
                    <div class="px-6 py-4 w-30 whitespace-nowrap">
                      <div v-for="(stock, index) in item.stock" :key="stock.id" class="w-full flex items-center justify-between">
                              <div v-if="index === 2">
                                <!-- Display the second quantity value here -->
                                {{ $number(stock.quantity) }} <span v-if="item.track_weight == 1">({{ $number(stock.weight) }})</span>
                              </div>
                            </div>


                    </div>
                  </td>
                        <td class="border-t" @click="goto(item)" v-if="$can('read-items')">
                    <div class="px-6 py-4 w-30 whitespace-nowrap">
                      <div v-for="(stock, index) in item.stock" :key="stock.id" class="w-full flex items-center justify-between">
                              <div v-if="index === 3">
                                 <!--Display the second quantity value here -->
                                {{ $number(stock.quantity) }} <span v-if="item.track_weight == 1">({{ $number(stock.weight) }})</span>
                              </div>
                            </div>


                    </div>
                  </td>
                        <td class="border-t" @click="goto(item)" v-if="$can('read-items')">
                    <div class="px-6 py-4 w-30 whitespace-nowrap">
                      <div v-for="(stock, index) in item.stock" :key="stock.id" class="w-full flex items-center justify-between">
                              <div v-if="index === 4">
                                 <!--Display the second quantity value here -->
                                {{ $number(stock.quantity) }} <span v-if="item.track_weight == 1">({{ $number(stock.weight) }})</span>
                              </div>
                            </div>


                    </div>
                  </td>
                   <td class="border-t" @click="goto(item)" v-if="$can('read-items')">
                    <div class="px-6 py-4 w-30 whitespace-nowrap">
                      <div v-for="(stock, index) in item.stock" :key="stock.id" class="w-full flex items-center justify-between">
                              <div v-if="index === 5">
                                 <!--Display the second quantity value here -->
                                {{ $number(stock.quantity) }} <span v-if="item.track_weight == 1">({{ $number(stock.weight) }})</span>
                              </div>
                            </div>


                    </div>
                  </td>
                  <td class="border-t" @click="goto(item)" v-if="$can('read-items')">
                      <div class="font-bold">
                        {{ $number(item.stock.reduce((a, c) => a + parseFloat(c.quantity), 0)) }}
                        <span v-if="item.track_weight == 1">({{ $number(item.stock.reduce((a, c) => a + parseFloat(c.weight), 0)) }})</span>
                      </div>
                    </td>
            <!-- <td class="border-t" @click="goto(item)" :class="{ 'cursor-pointer': $can('read-items') }">
              <div class="px-6 py-4 flex items-center max-w-lg min-w-56 w-64">
                <div class="w-full whitespace-normal line-clamp-4">
                  {{ item.details }}
                </div>
              </div>
            </td> -->
            <!--    </div>-->
            <!--  </div>-->
            <!--</td>-->
          </tr>
          <tr v-if="items.data.length === 0">
            <td class="border-t px-6 py-4" colspan="5">{{ $t('There is no data to display.') }}</td>
          </tr>
        </table>
      </div>
      <pagination class="mt-6" :meta="items.meta" :links="items.links" />
    </div>

 

    <!-- Delete User Confirmation Modal -->
  </admin-layout>
</template>

<script>
import pickBy from 'lodash/pickBy';
import throttle from 'lodash/throttle';
import mapValues from 'lodash/mapValues';
import ItemDetails from './Warehousee.vue';
import Dialog from '@/Shared/Dialog.vue';
import Modal from '@/Jetstream/Modal.vue';
import TecButton from '@/Jetstream/Button.vue';
import Pagination from '@/Shared/Pagination.vue';
import SelectInput from '@/Shared/SelectInput.vue';
import TecDropdown from '@/Jetstream/Dropdown.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import AutoComplete from '@/Shared/AutoComplete.vue';
import SearchFilter from '@/Shared/SearchFilter.vue';
import TecDropdownLink from '@/Jetstream/DropdownLink.vue';
import TecSectionTitle from '@/Jetstream/SectionTitle.vue';

export default {
  components: {
    Modal,
    Dialog,
    TecButton,
    Pagination,
    ItemDetails,
    AdminLayout,
    TecDropdown,
    SelectInput,
    AutoComplete,
    SearchFilter,
    TecDropdownLink,
    TecSectionTitle,
  },

  props: {
    items: Object,
    filters: Object,
    warehouse: Array,
  },

  data() {
    return {
      edit: null,
      item: null,
      close: false,
      confirm: false,
      details: false,
      loading: false,
      permanent: false,
      restoreConf: false,
      form: {
        search: this.filters.search,
        trashed: this.filters.trashed,
      },
    };
  },

  watch: {
    form: {
      handler: throttle(function () {
        let query = pickBy(this.form);
        this.$inertia.visit(this.route('reports.category', Object.keys(query).length ? query : { remember: 'forget' }), {
          onFinish: () => {
            document.getElementById('page-search').focus();
          },
        });
      }, 150),
      deep: true,
    },
  },

  methods: {
    goto(item) {
      // this.item = item;
      // this.details = true;
      if (this.item && this.item.id == item.id) {
        this.details = true;
      } else {
        this.loading = true;
        axios.get(route('items.show', item.id) + '?json=yes').then(res => {
          this.item = res.data;
          this.details = true;
          this.loading = false;
        });
      }
      // if (this.$can('read-items')) {
      //   this.$inertia.visit(route('items.show', id));
      // }
    },
    reset() {
      this.form = mapValues(this.form, () => null);
    },
  },
};
</script>
