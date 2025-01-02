<template>
  <admin-layout :title="$t(edit ? 'edit_x' : 'create_x', { x: $t('Checkout') })">
    <tec-form-section @submitted="submit">
      <template #title>
        <template v-if="edit">
          <div class="flex items-center">
            <inertia-link class="text-blue-600 hover:text-blue-700" :href="route('checkouts.index')">{{ $t('Checkouts') }}</inertia-link>
            <span class="text-blue-600 font-medium mx-2">/</span>
            {{ $t('Checkout') }} ({{ edit.reference }})
          </div>
        </template>
        <template v-else>
          {{ $t('create_x', { x: $t('Checkout') }) }}
        </template>
      </template>

      <template #description>{{
        edit ? $t('Update the record by modifying the details in the form below') : $t('Please fill the form below to add a new record.')
      }}</template>

      <template #form>
        <trashed-message v-if="edit && edit.deleted_at" class="mb-6" @restore="restore">
          {{ $t('This record has been deleted.') }}
        </trashed-message>
        <div class="flex flex-col gap-6">
          <div class="flex flex-col lg:flex-row gap-6">
            <div class="flex flex-col gap-6 w-full lg:w-1/2">
              <text-input type="date" v-model="form.date" :error="$page.props.errors.date" :label="$t('Date')" />
              <text-input v-model="form.reference" :error="$page.props.errors.reference" :label="$t('Reference')" />
            </div>
            <div class="flex flex-col gap-6 w-full lg:w-1/2">
              <!--<auto-complete-->
              <!--  id="contact"-->
              <!--  :label="$t('Contact')"-->
              <!--  :suggestions="contacts"-->
              <!--  v-model="form.contact_id"-->
              <!--  :error="$page.props.errors.contact_id"-->
              <!--/>-->
        <!--         <label :for="contact">{{ $t('Contact') }}</label>-->
        <!--<auto-complete v-model="form.contact_id" id="contact" :error="$page.props.errors.contact_id">-->
        <!--  <option v-for="contact in contacts" :key="contact.id" :value="contact.id">-->
        <!--    {{ contact.name }}-->
        <!--  </option>-->
        <!--</auto-complete>-->
                    <auto-complete
              id="contact"
              :label="$t('Contact')"
              :suggestions="contacts"
              v-model="form.contact_id"
              :error="$page.props.errors.contact_id"
              style="margin: 0; padding: 0;"
            >
              <option v-for="contact in contacts" :key="contact.id" :value="contact.id">
                {{ contact.name }}
              </option>
            </auto-complete>
              <template v-if="!$super && $user.warehouse_id">
                <text-input
                  disabled
                  readonly
                  :label="$t('Warehouse')"
                  :modelValue="warehouses.find(w => w.id == $user.warehouse_id)?.name"
                />
              </template>
              <template v-else>
                <auto-complete
                  id="warehouse"
                  :label="$t('Warehouse')"
                  :suggestions="warehouses"
                  v-model="form.warehouse_id"
                  :error="$page.props.errors.warehouse_id"
                />
              </template>
            </div>
          </div>
                    <div class="flex flex-col gap-6 w-full lg:w-1/2 border border-gray-300 rounded-md p-4">
                      <label class="block mb-2">Checkout To:</label>
                      <select v-model="checkoutType" class="border border-gray-300 rounded-md p-2">
                        <option value="installation">Installation</option>
                        <option value="repair">Repair</option>
                        <option value="service">Service</option>
                      </select>
              
              <!-- Installation section -->
                  <div v-if="checkoutType === 'installation'" class="mt-4">
                    <label class="block mb-2">Installation Type:</label>
                    <select v-model="installationType" class="border border-gray-300 rounded-md p-2 w-full">
                      <option value="customer">Customer</option>
                      <option value="network">Network</option>
                    </select>
                
                    <div v-if="installationType == 'customer'" class="mt-4">
                      <auto-complete
                        id="customer"
                        :label="$t('Customer')"
                        :suggestions="customersWithPhone"
                        v-model="form.customer_id"
                      >
                        <template #option="{ option }">
                          <div class="flex items-center justify-between">
                            <div>{{ option.label }}</div>
                          </div>
                        </template>
                      </auto-complete>
                    </div>
                
                <div v-else-if="installationType == 'network'" class="mt-4">
                  <label class="block mb-2">Network:</label>
                  <input type="text" v-model="form.network" class="border border-gray-300 rounded-md p-2 w-full">
                </div>
              </div>
              <!-- Repair section -->
              <div v-else-if="checkoutType === 'repair'" class="mt-4">
                <auto-complete
                  id="supplier"
                  :label="$t('Supplier')"
                  :suggestions="suppliers"
                  v-model="form.supplier_id"
                >
                  <template #option="{ option }">
                    <div class="flex items-center justify-between">
                      <div>{{ option.label }}</div>
                    </div>
                  </template>
                </auto-complete>
              </div>
            
              <!-- Service section -->
              <div v-else-if="checkoutType === 'service'" class="mt-4">
               <auto-complete
              id="contact"
              :label="$t('Vayudoot Person')"
              :suggestions="contacts"
              v-model="form.vayudoot_person_id"
              :error="$page.props.errors.contact_id"
              style="margin: 0; padding: 0;"
            >
              <option v-for="contact in contacts" :key="contact.id" :value="contact.id">
                {{ contact.name }}
              </option>
            </auto-complete>
              </div>
            </div>

          <div class="p-4 md:px-6 bg-gray-50 -mx-4 md:-mx-6">
            <auto-complete
              json
              keep-focus
              reset-search
              id="add-item"
              @update:model-value="itemSelected"
              :suggestions="route('items.search')"
              :placeholder="$t('Scan barcode or search items')"
              :defaultText="$t('Scan barcode or search items for next')"
            />

            <div v-if="smallScreen" class="pt-4">
              <div
                v-if="form.items.length === 0"
                :class="{ '-mx-4 md:-mx-6 -mb-4 p-4 bg-red-100 border-red-600': $page.props.errors.items }"
              >
                {{ $t('Add item to the list by search or scan barcode') }}
                <div v-if="$page.props.errors.items" class="text-red-600">{{ $page.props.errors.items }}</div>
              </div>
              <div v-else>
                <div
                  :key="item.id"
                  v-for="(item, ii) in form.items"
                  class="-mx-4 -mb-6 p-4 md:-mx-6 border-b border-blue-100"
                  :class="{
                    'bg-blue-50': ii % 2 == 0,
                    'bg-indigo-50': ii % 2 != 0,
                    error:
                      $page.props.errors['items.' + ii + '.variation_id'] ||
                      $page.props.errors['items.' + ii + '.quantity'] ||
                      $page.props.errors['items.' + ii + '.weight'],
                  }"
                >
                  <h4 class="text-base font-bold" :class="{ '-mb-4': item.has_variants }">{{ item.name }} ({{ item.code }})</h4>
                  <template v-if="item.has_variants && item.variants.length && item.selected.variations">
                    <div v-for="v in item.selected.variations" :key="v.sku">
                      <span class="mt-8 block" v-html="$meta(v.meta)" />
                      <div class="w-full block sm:flex items-center justify-stretch gap-4">
                        <div class="mt-4 grow flex items-center gap-4">
                          <text-input :label="$t('Quantity')" type="number" v-model="v.quantity" class="w-1/2" />
                          <div class="w-1/2">
                            <select-input :label="$t('Unit')" v-model="v.unit_id" class="w-full">
                              <option :value="item.unit.id">{{ item.unit.name }}</option>
                              <template v-if="item.unit.subunits && item.unit.subunits.length">
                                <option v-for="sub in item.unit.subunits" :key="sub.id" :value="sub.id">{{ sub.name }}</option>
                              </template>
                            </select-input>
                          </div>
                        </div>
                        <text-input
                          type="number"
                          v-model="v.weight"
                          :label="$t('Weight')"
                          class="mt-4 w-full sm:w-1/3"
                          v-if="$settings.track_weight == 1 && item.track_weight == 1"
                        />
                      </div>
                    </div>
                  </template>
                  <template v-else>
                    <div class="w-full block sm:flex items-center justify-stretch gap-4">
                      <div class="mt-4 grow flex items-center gap-4">
                        <text-input :label="$t('Quantity')" type="number" v-model="item.quantity" class="w-1/2" />
                        <div class="w-1/2">
                          <select-input :label="$t('Unit')" v-model="item.unit_id" class="w-full">
                            <option :value="item.unit.id">{{ item.unit.name }}</option>
                            <template v-if="item.unit.subunits && item.unit.subunits.length">
                              <option v-for="sub in item.unit.subunits" :key="sub.id" :value="sub.id">{{ sub.name }}</option>
                            </template>
                          </select-input>
                        </div>
                      </div>
                      <text-input
                        type="number"
                        :label="$t('Weight')"
                        v-model="item.weight"
                        class="mt-4 w-full sm:w-1/3"
                        v-if="$settings.track_weight == 1 && item.track_weight == 1"
                      />
                    </div>
                  </template>
                  <div class="mt-4">
                    <div v-if="$page.props.errors['items.' + ii + '.variation_id']" class="text-red-600 pt-1 rounded-md">
                      {{ $page.props.errors['items.' + ii + '.variation_id'].split('when').shift() }}.
                    </div>
                    <div v-if="$page.props.errors['items.' + ii + '.quantity']" class="text-red-600 pt-1 rounded-md">
                      {{ $page.props.errors['items.' + ii + '.quantity'] }}
                    </div>
                    <div v-if="$page.props.errors['items.' + ii + '.weight']" class="text-red-600 pt-1 rounded-md">
                      {{ $page.props.errors['items.' + ii + '.weight'].split('when').shift() }}.
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div v-else class="bg-white mt-4 rounded-md shadow overflow-x-auto">
              <table class="w-full">
                <tr class="text-left font-bold">
                  <th class="px-2 lg:pl-6 py-4 w-4"><icons name="trash" /></th>
                  <th class="px-2 lg:px-6 py-4">{{ $t('Item') }}</th>
                  <th class="px-2 lg:px-6 py-4 text-center" :class="$settings.track_weight ? 'w-32 xl:w-56' : 'w-px'">
                    <span v-if="$settings.track_weight">{{ $t('Weight') }}</span>
                  </th>
                  <th class="px-2 lg:px-6 py-4 text-center w-32 xl:w-56">{{ $t('Quantity') }}</th>
                  <th class="px-2 lg:px-6 py-4 text-center w-32 xl:w-56">{{ $t('Unit') }}</th>
                </tr>
                <template v-if="form.items.length">
                  <template v-for="(item, ii) in form.items" :key="item.id">
                    <template v-if="item.selected && item.selected.variations && item.selected.variations.length">
                      <tbody class="group">
                        <tr
                          class="group-hover:bg-gray-100 focus-within:bg-gray-100"
                          :class="{
                            error:
                              $page.props.errors['items.' + ii + '.variation_id'] ||
                              $page.props.errors['items.' + ii + '.quantity'] ||
                              $page.props.errors['items.' + ii + '.weight'],
                          }"
                        >
                          <td class="border-t">
                            <div class="px-2 lg:pl-6 pb-2 focus:text-indigo-500"></div>
                          </td>
                          <td class="border-t" colspan="4">
                            <div class="px-2 lg:px-6 py-2 focus:text-indigo-500">
                              <!-- <button
                                type="button"
                                class="w-full text-left rounded-md transition-all hover:bg-blue-100 -my-1 py-1 hover:pl-5 focus:outline-none focus:ring focus:ring-gray-300 focus:bg-blue-100"
                              > -->
                              <h4 class="w-full lg:w-auto font-bold">
                                <span class="text-base">{{ item.name }} ({{ item.code }})</span>
                              </h4>
                              <!-- </button> -->
                            </div>
                          </td>
                        </tr>
                        <tr
                          :key="variation.id"
                          v-for="(variation, vi) in item.selected.variations"
                          class="group-hover:bg-gray-100 focus-within:bg-gray-100"
                          :class="{
                            error:
                              $page.props.errors['items.' + ii + '.variation_id'] ||
                              $page.props.errors['items.' + ii + '.quantity'] ||
                              $page.props.errors['items.' + ii + '.weight'],
                          }"
                        >
                          <td>
                            <div class="px-2 lg:pl-6 pb-2 focus:text-indigo-500">
                              <button
                                type="button"
                                @click="removeItem(item, ii, vi)"
                                class="text-red-400 hover:text-red-600 w-5 h-5 flex items-center justify-center"
                              >
                                <icons name="trash" />
                              </button>
                            </div>
                          </td>
                          <td>
                            <div class="px-2 lg:px-6 pb-2 focus:text-indigo-500">
                              <div v-html="$meta(variation.meta)"></div>
                            </div>
                          </td>
                          <td>
                            <div class="px-2 xl:px-6 pb-2 text-right" v-if="$settings.track_weight == 1 && item.track_weight == 1">
                              <text-input type="number" v-model="variation.weight" size="small" class="w-full block" />
                            </div>
                          </td>
                          <td>
                            <div class="px-2 xl:px-6 pb-2 text-right">
                              <text-input type="number" v-model="variation.quantity" size="small" class="w-full block" />
                            </div>
                          </td>
                          <td>
                            <div class="px-2 xl:px-6 pb-2 text-right" v-if="item.unit">
                              <select-input v-model="variation.unit_id" size="small" class="w-full block">
                                <option :value="item.unit.id">{{ item.unit.name }}</option>
                                <template v-if="item.unit.subunits && item.unit.subunits.length">
                                  <option v-for="sub in item.unit.subunits" :key="sub.id" :value="sub.id">{{ sub.name }}</option>
                                </template>
                              </select-input>
                            </div>
                          </td>
                        </tr>
                        <tr
                          v-if="
                            $page.props.errors['items.' + ii + '.variation_id'] ||
                            $page.props.errors['items.' + ii + '.quantity'] ||
                            $page.props.errors['items.' + ii + '.weight']
                          "
                          class="group-hover:bg-gray-100 focus-within:bg-gray-100"
                          :class="{
                            error:
                              $page.props.errors['items.' + ii + '.variation_id'] ||
                              $page.props.errors['items.' + ii + '.quantity'] ||
                              $page.props.errors['items.' + ii + '.weight'],
                          }"
                        >
                          <td>
                            <div class="px-2 lg:pl-6 pb-2 focus:text-indigo-500"></div>
                          </td>
                          <td colspan="4">
                            <div class="px-2 lg:px-6 pb-2 focus:text-indigo-500">
                              <div v-if="$page.props.errors['items.' + ii + '.variation_id']" class="text-red-600 pt-1 rounded-md">
                                {{ $page.props.errors['items.' + ii + '.variation_id'].split('when').shift() }}.
                              </div>
                              <div v-if="$page.props.errors['items.' + ii + '.quantity']" class="text-red-600 pt-1 rounded-md">
                                {{ $page.props.errors['items.' + ii + '.quantity'] }}
                              </div>
                              <div v-if="$page.props.errors['items.' + ii + '.weight']" class="text-red-600 pt-1 rounded-md">
                                {{ $page.props.errors['items.' + ii + '.weight'].split('when').shift() }}.
                              </div>
                            </div>
                          </td>
                        </tr>
                      </tbody>
                    </template>
                    <tr
                      v-else
                      class="hover:bg-gray-100 focus-within:bg-gray-100"
                      :class="{
                        error:
                          $page.props.errors['items.' + ii + '.variation_id'] ||
                          $page.props.errors['items.' + ii + '.quantity'] ||
                          $page.props.errors['items.' + ii + '.weight'],
                      }"
                    >
                      <td class="border-t">
                        <div class="px-2 lg:pl-6 py-2 focus:text-indigo-500">
                          <button
                            type="button"
                            @click="removeItem(item, ii)"
                            class="text-red-400 hover:text-red-600 w-5 h-5 flex items-center justify-center"
                          >
                            <icons name="trash" />
                          </button>
                        </div>
                      </td>
                      <td class="border-t">
                        <div class="px-2 lg:px-6 py-2 focus:text-indigo-500">
                          <!-- <button
                            type="button"
                            class="w-full text-left rounded-md hover:bg-blue-100 -m-1 p-1 lg:-mx-5 lg:px-5 focus:outline-none focus:ring focus:ring-gray-300 focus:bg-blue-100"
                          > -->
                          <h4 class="w-full lg:w-auto">
                            <span class="text-base">{{ item.name }} ({{ item.code }})</span>
                          </h4>
                            <div v-if="item.mac_addresses && item.mac_addresses !== '0000000000'">
                            <label for="macAddressDropdown">MAC Address:</label>
                            <select id="macAddressDropdown"
                                    title="Click to see MAC addresses"
                                    style="border: 1px solid #ccc; padding: 5px; border-radius: 5px; width: 200px; margin-bottom: 10px;">
                                <template v-for="mac_address in item.mac_addresses.split(',')">
                                    <option :value="mac_address.trim()">{{ mac_address.trim() }}</option>
                                </template>
                            </select>
                        </div>
                        
                        <div v-if="item.serial_numbers && item.serial_numbers !== '000000'">
                            <label for="serialNumberDropdown">Serial Number:</label>
                            <select id="serialNumberDropdown"
                                    title="Click to see serial numbers"
                                    style="border: 1px solid #ccc; padding: 5px; border-radius: 5px; width: 200px; margin-bottom: 10px;">
                                <template v-for="serial_number in item.serial_numbers.split(',')">
                                    <option :value="serial_number.trim()">{{ serial_number.trim() }}</option>
                                </template>
                            </select>
                        </div>
                        
                        <div v-if="item.warranty_end_dates && item.warranty_end_dates !== '2024-12-31'">
                            <label for="warrantyEndDateDropdown">Warranty End Date:</label>
                            <select id="warrantyEndDateDropdown"
                                    title="Click to see warranty end dates"
                                    style="border: 1px solid #ccc; padding: 5px; border-radius: 5px; width: 200px; margin-bottom: 10px;">
                                <template v-for="warranty_end_date in item.warranty_end_dates.split(',')">
                                    <option :value="warranty_end_date.trim()">{{ warranty_end_date.trim() }}</option>
                                </template>
                            </select>
                        </div>

                          <!-- </button> -->

                          <div v-if="$page.props.errors['items.' + ii + '.variation_id']" class="text-red-600 pt-1 rounded-md">
                            {{ $page.props.errors['items.' + ii + '.variation_id'].split('when').shift() }}.
                          </div>
                          <div v-if="$page.props.errors['items.' + ii + '.quantity']" class="text-red-600 pt-1 rounded-md">
                            {{ $page.props.errors['items.' + ii + '.quantity'] }}
                          </div>
                          <div v-if="$page.props.errors['items.' + ii + '.weight']" class="text-red-600 pt-1 rounded-md">
                            {{ $page.props.errors['items.' + ii + '.weight'].split('when').shift() }}.
                          </div>
                        </div>
                      </td>
                      <td class="border-t">
                        <div class="px-2 xl:px-6 py-2 text-right" v-if="$settings.track_weight == 1 && item.track_weight == 1">
                          <text-input type="number" v-model="item.weight" size="small" class="w-full block" />
                        </div>
                      </td>
                      <td class="border-t">
                        <div class="px-2 xl:px-6 py-2 text-right">
                          <text-input type="number" v-model="item.quantity" size="small" class="w-full block" />
                        </div>
                      </td>
                      <td class="border-t">
                        <div class="px-2 xl:px-6 py-2 text-right" v-if="item.unit">
                          <select-input v-model="item.unit_id" size="small" class="w-full block">
                            <option :value="item.unit.id">{{ item.unit.name }}</option>
                            <template v-if="item.unit.subunits && item.unit.subunits.length">
                              <option v-for="sub in item.unit.subunits" :key="sub.id" :value="sub.id">{{ sub.name }}</option>
                            </template>
                          </select-input>
                        </div>
                      </td>
                    </tr>
                  </template>
                </template>
                <tr v-if="form.items.length === 0">
                  <td class="border-t px-2 lg:px-6 py-4" colspan="5" :class="{ 'bg-red-100 border-red-600': $page.props.errors.items }">
                    {{ $t('Add item to the list by search or scan barcode') }}
                    <div v-if="$page.props.errors.items" class="text-red-600">{{ $page.props.errors.items }}</div>
                  </td>
                </tr>
              </table>
            </div>
          </div>
          <div>
            <label for="file-upload" class="font-medium text-gray-700">{{ $t('Attachments') }}</label>
            <div v-if="edit && edit.attachments && edit.attachments.length" class="print:hidden py-4 w-full">
              <ul role="list" class="border border-gray-200 rounded-md divide-y divide-gray-200">
                <li
                  v-for="attachment in edit.attachments"
                  :key="attachment.id"
                  class="pl-3 pr-4 py-3 flex items-center justify-between text-sm"
                >
                  <div class="w-0 flex-1 flex items-center">
                    <icons name="clip" class="flex-shrink-0 h-5 w-5 text-gray-400" />
                    <span class="ml-2 flex-1 w-0 truncate"> {{ attachment.title }} </span>
                  </div>
                  <div class="ml-4 flex-shrink-0 flex items-center gap-4">
                    <a class="font-medium text-indigo-600 hover:text-indigo-500" :href="route('media.download', attachment.id)">
                      {{ $t('Download') }}
                    </a>
                    <button class="font-medium text-red-600 hover:text-red-500" @click="deleteAttachment(attachment.id)">
                      {{ $t('Delete') }}
                    </button>
                  </div>
                </li>
              </ul>
            </div>
            <div
              :class="$page.props.errors.excel ? 'border-red-500' : 'border-gray-300'"
              class="mt-1 flex justify-center px-6 py-3 border-2 border-dashed rounded-md"
            >
              <div class="space-y-1 text-center">
                <div class="flex items-center justify-center text-gray-600">
                  <label
                    for="file-upload"
                    class="relative cursor-pointer bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-gray-300"
                  >
                    <span v-if="files.length" class="font-semibold">{{ $t('Add more files') }}</span>
                    <span v-else class="font-semibold">{{ $t('Select files') }}</span>
                    <input
                      multiple
                      ref="file"
                      type="file"
                      class="sr-only"
                      id="file-upload"
                      name="file-upload"
                      @change="updateFile"
                      accept=".png,.jpg,.jpeg,.pdf,.doc,.docx,.xls,.xlsx,.zip"
                    />
                  </label>
                  <p class="pl-1">{{ $t('or drag and drop') }}</p>
                </div>
                <div class="text-sm text-gray-700">
                  <div>
                    {{ $t('You can select .png, .jpg, .pdf, .docx, .xlsx & .zip files.') }}
                  </div>
                </div>
                <div v-if="files.length" class="inline-block pt-4">
                  <div class="px-3 py-1 rounded-md border font-bold text-base">
                    {{ $t('Selected Files') }}:
                    <div class="text-left" v-for="(f, fi) in files" :key="fi">{{ fi + 1 }}. {{ f }}</div>
                  </div>
                </div>
                <div v-if="$page.props.errors.excel" class="mt-4 pt-2 text-red-600 rounded-md">
                  {{ $page.props.errors.files }}
                </div>
              </div>
            </div>
          </div>
          <textarea-input v-model="form.details" :error="$page.props.errors.details" :label="$t('Details')" />
          <div>
            <check-box
              id="draft"
              class="mb-2"
              v-model:checked="form.draft"
              v-if="!edit || edit.draft == 1"
              :error="$page.props.errors.draft"
              :label="$t('This record is draft')"
            />
          </div>
        </div>
      </template>

      <template #actions>
        <div class="w-full flex items-center justify-between">
          <template v-if="edit">
            <button
              type="button"
              @click="destroy"
              v-if="!edit.deleted_at"
              class="text-red-600 px-4 py-2 rounded border-2 border-transparent hover:border-gray-300 focus:outline-none focus:border-gray-300"
            >
              {{ $t('delete_x', { x: $t('Checkout') }) }}
            </button>
            <button
              v-else
              type="button"
              @click="deletePermanently"
              class="text-red-600 px-4 py-2 rounded border-2 border-transparent hover:border-gray-300 focus:outline-none focus:border-gray-300"
            >
              {{ $t('delete_x', { x: $t('Permanently') }) }}
            </button>
          </template>
          <div v-else></div>
          <div class="flex items-center">
            <tec-action-message :on="form.recentlySuccessful" class="mx-3">{{ $t('Saved.') }}</tec-action-message>
            <loading-button type="submit" :loading="form.processing" :disabled="form.processing">{{ $t('Save') }}</loading-button>
          </div>
        </div>
      </template>
    </tec-form-section>

    <!-- Select Variation Modal -->
    <select-variant-modal
      v-if="select_variant"
      :show="select_variant"
      :nf="unknown_variation"
      :variants="item.variants"
      @selected="variantSelected"
      @close="select_variant = false"
    />

    <!-- Delete User Confirmation Modal -->
    <Dialog
      max-width="md"
      :show="permanent"
      action-type="delete"
      title="Delete Checkout?"
      :close="closePermanentModal"
      action-text="Delete Permanently"
      :action="deleteCategoryPermanently"
      :content="`<p class='mb-2'>${$t('Are you sure you want to delete the record permanently?')}</p>
        <p>${$t('Once deleted, all of its resources and data will be permanently deleted.')}</p>`"
    />

    <!-- Delete Account Confirmation Modal -->
    <Dialog
      :show="confirm"
      :close="closeModal"
      :action="deleteItem"
      action-type="delete"
      title="Delete Checkout?"
      action-text="Delete Checkout"
      :content="$t('Are you sure you want to delete the record?')"
    />

    <!-- Restore Account Confirmation Modal -->
    <Dialog
      :show="restoreConf"
      :action="restoreItem"
      title="Restore Checkout!"
      :close="closeRestoreModal"
      action-text="Restore Checkout"
      :content="$t('Are you sure you want to restore the record?')"
    />
  </admin-layout>
</template>

<script>
import _isEqual from 'lodash/isEqual';
import throttle from 'lodash/throttle';
import Dialog from '@/Shared/Dialog.vue';
import CheckBox from '@/Shared/CheckBox.vue';
import TextInput from '@/Shared/TextInput.vue';
import SelectInput from '@/Shared/SelectInput.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import AutoComplete from '@/Shared/AutoComplete.vue';
import LoadingButton from '@/Shared/LoadingButton.vue';
import TextareaInput from '@/Shared/TextareaInput.vue';
import TecFormSection from '@/Jetstream/FormSection.vue';
import TrashedMessage from '@/Shared/TrashedMessage.vue';
import TecActionMessage from '@/Jetstream/ActionMessage.vue';
import SelectVariantModal from '@/Shared/SelectVariantModal.vue';
import TecSecondaryButton from '@/Jetstream/SecondaryButton.vue';

export default {
  props: ['edit', 'contacts', 'warehouses', 'customers', 'suppliers'],

  components: {
    Dialog,
    CheckBox,
    TextInput,
    SelectInput,
    AdminLayout,
    AutoComplete,
    LoadingButton,
    TextareaInput,
    TecFormSection,
    TrashedMessage,
    TecActionMessage,
    SelectVariantModal,
    TecSecondaryButton,
  },

  data() {
    return {
      checkoutType: 'installation',
      installationType: this.edit ? (this.edit.customer_id ? 'customer' : 'network') : 'customer',
      files: [],
      item: null,
      confirm: false,
      selected: false,
      permanent: false,
      restoreConf: false,
      select_variant: false,
      unknown_variation: null,
      wIW: window.innerWidth,
      form: this.$inertia.form({
        _method: this.edit ? 'PUT' : 'POST',
        items: [],
        attachments: null,
        customer_id: this.edit ? this.edit.customer_id : null,
        network: this.edit ? this.edit.network : null,
        supplier_id: this.edit ? this.edit.supplier_id : null,
        contact_id: this.edit ? this.edit.contact_id : null,
        vayudoot_person_id: this.edit ? this.edit.vayudoot_person_id : null,
        customersWithPhone: [],
        suppliers: this.edit ? this.edit.suppliers : null,
        contacts: this.edit ? this.edit.contacts : null,
        checkoutType: this.edit ? this.edit.checkoutType : '',
        details: this.edit ? this.edit.details : null,
        draft: this.edit ? this.edit.draft == 1 : false,
        reference: this.edit ? this.edit.reference : null,
        date: this.edit ? this.edit.date : this.$formatJSDate(new Date()),
        warehouse_id: this.edit ? this.edit.warehouse_id : this.$super ? null : this.$user?.warehouse_id,
      }),
    };
  },

  computed: {
    smallScreen() {
      return this.wIW < 1024;
    },
    customersWithPhone() {
      if (this.customers) {
        return this.customers.map((customer) => ({
          id: customer.id,
          label: `${customer.name} (${customer.phone})`,
        }));
      } else {
        return [];
      }
    },
  },

  watch: {
    select_variant(show) {
      this.unknown_variation = null;
      document.body.style.overflow = show ? 'hidden' : 'auto';
    },
  },

  created() {
    if (this.edit) {
      console.log('Edit data:', this.edit);
      this.checkoutType = this.edit.checkoutType;
      this.form.customer_id = this.edit.customer_id;
      this.form.network = this.edit.network;
      this.form.supplier_id = this.edit.supplier_id;
      this.form.vayudoot_person_id = this.edit.vayudoot_person_id;
     this.form.items = this.edit.items.map(i => {
        let item = {
          ...i.item,
          id: i.id,
          item_id: i.item_id,
          unit_id: i.unit_id,
          weight: parseFloat(i.weight),
          quantity: parseFloat(i.quantity),
          old_quantity: parseFloat(i.quantity),
          selected: {
            serials: [],
              variations: i.variations.map(v => {
              let variations = {
                ...v,
                weight: parseFloat(v.pivot.weight),
                unit_id: v.pivot.unit_id || i.unit_id,
                quantity: parseFloat(v.pivot.quantity),
                old_quantity: parseFloat(v.pivot.quantity),
              };
              return variations;
            }),
          },
        };
        console.log('Item:', item);
        console.log('Item:', item);
      // Check for item name and code
      if (!item.name) {
        console.warn('Item name is missing:', item);
      }
      if (!item.code) {
        console.warn('Item code is missing:', item);
      }
        return item;
      });
    }
  },

  mounted() {
    if (!this.edit && !this.$super) {
      this.form.warehouse_id = this.$user.warehouse_id;
    }
    window.addEventListener('resize', this.onResize);
  },

  unmounted() {
    window.removeEventListener('resize', this.onResize);
  },

  methods: {
      hasDetails(item) {
      return item.mac_address || item.serial_number || item.warranty_end_date;
    },
    onResize: throttle(function () {
      this.wIW = window.innerWidth;
    }, 250),
    removeItem(item, ii, vi) {
      if (vi) {
        item.selected.variations.splice(vi, 1);
        if (item.selected.variations.length < 1) {
          item.selected.variations = [];
          this.form.items.splice(ii, 1);
        }
      } else {
        this.form.items.splice(ii, 1);
      }
    },
    variantSelected(meta) {
      let variation = null;
      if (typeof meta === 'object' && meta !== null) {
        variation = this.item.variations.find(v => _isEqual(v.meta, meta));
      } else {
         variation = this.item.variations.find(v => v.sku == meta);
      }
      if (variation) {
        if (!this.item.selected) {
          this.item.selected = { variations: [], serials: [] };
        }
        variation.quantity = 1;
        variation.unit_id = this.item.unit_id;
        variation.weight = this.$settings.track_weight == 1 && this.item.track_weight == 1 ? 1 : 0;
        let item = this.form.items.find(i => i.item_id == this.item.id);
        if (item) {
          item.quantity += 1;
          item.weight += this.$settings.track_weight == 1 && item.track_weight == 1 ? 1 : 0;
          let exist = item.selected.variations.length ? item.selected.variations.find(v => v.id == variation.id) : null;
          if (exist) {
            exist.quantity += 1;
            exist.weight += this.$settings.track_weight == 1 && item.track_weight == 1 ? 1 : 0;
          } else {
            item.selected.variations.push(variation);
          }
        } else {
          this.item.selected.variations = [{ ...variation }];
          this.form.items.push({
            ...this.item,
            quantity: 1,
            unit_id: this.item.unit_id,
            weight: this.$settings.track_weight == 1 && this.item.track_weight == 1 ? 1 : 0,
          });
        }
        this.item = null;
        this.select_variant = false;
      } else {
        this.unknown_variation = this.$t('No match found for the item variation.');
        this.$page.props.flash.error = this.unknown_variation;
      }
    },
    itemSelected(v) {
      v.item_id = v.id;
      v.selected = v.selected || { variations: [], serials: [] };
      if (v.has_variants && v.variants.length > 0) {
       this.item = { ...v, variants: v.variants.map(vr => ({ ...vr, selected: null })) };
        this.select_variant = true;
      } else {
        let item = this.form.items.find(i => i.id == v.id);
        if (item) {
          item.quantity += 1;
          item.weight += this.$settings.track_weight == 1 && item.track_weight == 1 ? 1 : 0;
        } else {
          this.form.items.push({ ...v, quantity: 1, unit_id: v.unit_id, weight: v.track_weight == 1 ? 1 : 0 });
        }
      }
    },
    updateFile(e) {
     Array.from(e.target.files).forEach(file => this.files.push(file.name));
    },
    submit() {
      if (this.$refs.file) {
        this.form.attachments = this.$refs.file.files;
      }
      this.form.network = this.form.network;
      this.form.vayudoot_person_id = this.form.vayudoot_person_id;
      this.form.contact_id = this.form.contact_id;
      this.form.checkoutType = this.checkoutType;
      this.form
        .transform(data => ({
          ...data,
          items: data.items.map(i => ({
            ...i,
            unit: null,
            variants: null,
            variations: null,
            unit_id: i.unit_id || null,
            selected: {
              serials: i.selected.serials && i.selected.serials.length ? i.selected.serials.map(s => s.id) : [],
              variations: i.selected.variations.map(v => {
                let fv = {};
                return (fv[v.id] = {
                  weight: v.weight,
                  variation_id: v.id,
                  quantity: v.quantity,
                  unit_id: v.unit_id || null,
                  old_quantity: v.old_quantity,
                });
              }),
            },
          })),
        }))
        .post(this.edit ? route('checkouts.update', this.edit.id) : route('checkouts.store'), { preserveScroll: true });
    },
    destroy() {
      this.confirm = true;
    },
    deleteItem() {
      this.form.delete(route('checkouts.destroy', this.edit.id), {
        onSuccess: () => this.closeModal(),
      });
    },
    closeModal() {
      this.confirm = false;
    },
    restore() {
      this.restoreConf = true;
    },
    restoreItem() {
      this.$inertia.put(this.route('checkouts.restore', this.edit.id), {
        onSuccess: () => (this.restoreConf = false),
      });
    },
    closeRestoreModal() {
      this.restoreConf = false;
    },
    deletePermanently() {
      this.permanent = true;
    },
    deleteCategoryPermanently() {
      this.form.delete(route('checkouts.destroy.permanently', this.edit.id), {
        onSuccess: () => this.closeModal(),
      });
    },
    closePermanentModal() {
      this.permanent = false;
    },
    deleteAttachment(id) {
      this.$inertia.form({ _method: 'DELETE' }).delete(route('media.delete', id));
    },
  },
};
</script>
