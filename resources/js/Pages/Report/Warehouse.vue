
<template>
  <admin-layout :title="$t('Warehouse')">
    <div class="px-4 md:px-0">
      <tec-section-title class="-mx-4 md:mx-0 mb-6">
        <template #title>{{ $t('Warehouse Report') }}</template>
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
          <tec-dropdown align="right" width="48">
      <template #trigger>
        <button
          class="flex items-center px-4 py-3 bg-gray-800 rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 focus:outline-none focus:ring focus:ring-gray-300 transition ease-in-out duration-150"
        >
          <icons name="menu"></icons>
        </button>
      </template>

      <template #content>
        <button @click="exportToExcel" class="block px-4 py-2 leading-5 text-gray-700 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 transition duration-150 ease-in-out">
          {{ $t('Export All') }}
        </button>
        <button @click="exportToCurrentExcel" class="block px-4 py-2 leading-5 text-gray-700 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 transition duration-150 ease-in-out">
          {{ $t('Export CurrentExcel') }}
        </button>
      </template>
    </tec-dropdown>
      </div>
      <div id="dd-table" class="bg-white -mx-4 md:mx-0 md:rounded-md shadow overflow-y-visible overflow-x-auto">
        <table class="w-full overflow-y-visible">
          <tr class="text-left font-bold">
            <th class="px-6 pt-6 pb-4">{{ $t('Name') }}</th>
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
                    <div class="px-6 py-4 w-56 flex items-center focus:text-indigo-500">
                      <img v-if="item.photo" class="block w-5 h-5 rounded-full mr-2 -my-2" :src="item.photo" />
                      <div>
                        <div class="font-bold">{{ item.name }}</div>
                      </div>
                      <icons v-if="item.deleted_at" name="trash" class="shrink-0 w-4 h-4 text-red-500 ml-2" />
                    </div>
                  </td>
                  <!-- Add cells for each warehouse -->
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
                  </tr>
                 
          <tr v-if="items.data.length === 0">
            <td class="border-t px-6 py-4" colspan="5">{{ $t('There is no data to display.') }}</td>
          </tr>
        </table>
      </div>
      <pagination class="mt-6" :meta="items.meta" :links="items.links" />
    </div>
    <loading v-if="loading" />
  </admin-layout>
</template>

<script>
import XLSX from 'xlsx';
import { utils, write } from 'xlsx';
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
    // ReportWarehousee,
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
        this.$inertia.visit(this.route('reports.warehouse', Object.keys(query).length ? query : { remember: 'forget' }), {
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
    this.item = item;
    this.details = true;
    if (this.item && this.item.id == item.id) {
      this.details = true;
    } else {
      this.loading = true;
      axios.get(route('reports.show', item.id) + '?json=yes').then(res => {
        this.item = res.data;
        this.details = true;
        this.loading = false;
      });
    }
  },
async exportToExcel() {
  try {
      const data = [];
      const table = document.querySelector('table');
    
      // Extract the table headers
      const headers = [];
      table.querySelectorAll('th').forEach((header) => {
        headers.push(header.innerText);
      });
      data.push(headers);
    
      // Loop through each row in the table
      table.querySelectorAll('tr').forEach((row) => {
        const rowData = [];
    
        // Loop through each cell in the row
        row.querySelectorAll('td').forEach((cell) => {
          rowData.push(cell.innerText);
        });
    
        // Add the row data to the data array
        data.push(rowData);
      });
    
      // Calculate the maximum width of each column
      const columnWidths = data.reduce((acc, rowData) => {
        rowData.forEach((cellValue, columnIndex) => {
          const valueLength = cellValue.length;
          if (!acc[columnIndex] || valueLength > acc[columnIndex]) {
            acc[columnIndex] = valueLength;
          }
        });
        return acc;
      }, []);
    
      // Create the worksheet from the extracted data
      const worksheet = utils.aoa_to_sheet(data);
    
      // Set the column widths based on the maximum width of each column
      worksheet['!cols'] = columnWidths.map((width) => ({ wch: width + 2 })); // Add 2 for padding
  
    // Fetch the Excel file data using Axios
    const response = await axios.get(route('reports.export-warehouse'), { responseType: 'blob' });

    // Create a Blob object and a download link
    const excelBlob = new Blob([response.data], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
    const downloadLink = document.createElement('a');

    // Specify the file name with .xlsx extension
    downloadLink.href = URL.createObjectURL(excelBlob);
    downloadLink.download = 'warehouses.xlsx';

    // Create a download link and trigger the download
    downloadLink.click();
  } catch (error) {
    console.error('Error exporting data:', error);
  }
},


    
     exportToCurrentExcel() {
      const data = [];
      const table = document.querySelector('table');
    
      // Extract the table headers
      const headers = [];
      table.querySelectorAll('th').forEach((header) => {
        headers.push(header.innerText);
      });
      data.push(headers);
    
      // Loop through each row in the table
      table.querySelectorAll('tr').forEach((row) => {
        const rowData = [];
    
        // Loop through each cell in the row
        row.querySelectorAll('td').forEach((cell) => {
          rowData.push(cell.innerText);
        });
    
        // Add the row data to the data array
        data.push(rowData);
      });
    
      // Calculate the maximum width of each column
      const columnWidths = data.reduce((acc, rowData) => {
        rowData.forEach((cellValue, columnIndex) => {
          const valueLength = cellValue.length;
          if (!acc[columnIndex] || valueLength > acc[columnIndex]) {
            acc[columnIndex] = valueLength;
          }
        });
        return acc;
      }, []);
    
      // Create the worksheet from the extracted data
      const worksheet = utils.aoa_to_sheet(data);
    
      // Set the column widths based on the maximum width of each column
      worksheet['!cols'] = columnWidths.map((width) => ({ wch: width + 2 })); // Add 2 for padding
    
      // Create the workbook and add the worksheet to it
      const workbook = utils.book_new();
      utils.book_append_sheet(workbook, worksheet, 'Warehouse Data');
    
      // Generate a Blob object from the workbook
      const excelBuffer = write(workbook, { type: 'array' });
      const excelBlob = new Blob([excelBuffer], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
    
      // Create a download link and trigger the download
      const downloadLink = document.createElement('a');
      downloadLink.href = URL.createObjectURL(excelBlob);
      downloadLink.download = 'warehouse_data_report.xlsx';
      downloadLink.click();
    },
 
    reset() {
      this.form = mapValues(this.form, () => null);
    },
// },
  },
};
</script>
