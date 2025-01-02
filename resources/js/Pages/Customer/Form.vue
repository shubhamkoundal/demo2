<template>
  <admin-layout :title="$t(edit ? 'edit_x' : 'create_x', { x: $t('Customer') })">
    <tec-form-section @submitted="update">
      <template #title>
        <div class="flex items-center">
          <template v-if="edit">
            <inertia-link class="text-blue-600 hover:text-blue-700" :href="route('customers.index')">{{ $t('Customers') }}</inertia-link>
            <span class="text-blue-600 font-medium mx-2">/</span>
            <img v-if="edit.photo" class="block w-6 h-6 rounded-full mr-2" :src="edit.photo" />
            {{ edit.name }}
          </template>
          <template v-else>
            {{ $t('create_x', { x: $t('Customer') }) }}
          </template>
        </div>
      </template>

      <template #description>{{
        edit ? $t('Update the record by modifying the details in the form below') : $t('Please fill the form below to add new record.')
      }}</template>

        <template #form>
          <trashed-message v-if="edit && edit.deleted_at" class="mb-6" @restore="restore">
            {{ $t('This record has been deleted.') }}
          </trashed-message>
           <div class="flex flex-col gap-y-6">
          <text-input v-model="form.name" :error="$page.props.errors.name" :label="$t('Name')" />
          <text-input type="email" v-model="form.email" :error="$page.props.errors.email" :label="$t('Email')" />
          <text-input v-model="form.phone" :error="$page.props.errors.phone" :label="$t('Phone')" />
          <text-input v-model="form.customerId" :error="$page.props.errors.customerId" :label="$t('Customer ID')" />
          <label for="isp" class="form-label">{{ $t('ISP') }}</label>
          <div class="form-label flex flex-col gap-y-6">
             <select :id="ispId" v-model="form.isp" class="form-select">
               <!--<option value=" "select>-- {{ $t('Select ISP') }} --</option>-->
               <option :value="null" disabled >{{ $t('Select ISP') }}</option>
               <option value="Vaydoot">Vaydoot</option>
               <option value="BSNL">BSNL</option>
               <!-- Add more ISP options as needed -->
              </select>
             <!--</div>-->
               <span class="text-red-500" v-if="$page.props.errors.isp">{{ $page.props.errors.isp }}</span>

             <textarea-input v-model="form.details" :error="$page.props.errors.details" :label="$t('Details')" />
           </div>
           </div>
<!--<div>-->
<!--  <div class="table-responsive">-->
    <!-- Display items -->
<!--    <h2>Items</h2>-->
<!--    <table class="table table-striped">-->
<!--      <thead>-->
<!--        <tr>-->
<!--          <th scope="col-md-2">Item Name</th>-->
<!--          <th scope="col-md-4">Item Quantity</th>-->
<!--          <th scope="col-md-6">Date</th>-->
<!--          <th scope="col-md-8">Details</th>-->
<!--        </tr>-->
<!--      </thead>-->
<!--      <tbody>-->
<!--        <tr v-for="item in items" :key="item.id">-->
<!--          <td>{{ item.name }}</td>-->
<!--          <td>{{ item.quantity }}</td>-->
<!--          <td>{{ $date(item.date) }}</td>-->
<!--          <td>{{ item.details }}</td>-->
<!--        </tr>-->
<!--      </tbody>-->
<!--    </table>-->
<!--  </div>-->
<!--</div>-->
<!--aaaaaa-->
 <div v-if="edit" class="bg-white -mx-4 md:mx-0 md:rounded-md shadow overflow-x-auto">
        <table class="w-full whitespace-nowrap">
          <tr class="text-left font-bold">
            <th class="px-6 pt-6 pb-4">{{ $t('item Name') }}</th>
            <th class="px-6 pt-6 pb-4">{{ $t('Item Quantity') }}</th>
            <th class="px-6 pt-6 pb-4">{{ $t('Date') }}</th>
            <th class="px-6 pt-6 pb-4" colspan="2">{{ $t('Details') }}</th>
          </tr>
         <tr v-for="item in items" :key="item.id">
               <td class="border-t">
              <div class="px-6 py-4 flex items-center focus:text-indigo-500">
               {{ item.name }}
               
              </div>
            </td>
            <td class="border-t">
              <div class="px-6 py-4 flex items-center focus:text-indigo-500">
              {{ item.quantity }}
               
              </div>
            </td>
            <td class="border-t">
              <div class="px-6 py-4 flex items-center">
               {{ $date(item.date) }}
              </div>
            </td>
            <td class="border-t">
              <div class="px-6 py-4 flex items-center">
               {{ item.details }}
              </div>
            </td>
          </tr>
        </table>
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
              {{ $t('delete_x', { x: $t('Customer') }) }}
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
            <tec-action-message :on="form.recentlySuccessful" class="mr-3">{{ $t('Saved.') }}</tec-action-message>
            <loading-button type="submit" :loading="form.processing" :disabled="form.processing">{{ $t('Save') }}</loading-button>
          </div>
        </div>
      </template>
    </tec-form-section>

    <!-- Delete User Confirmation Modal -->
    <Dialog
      max-width="md"
      :show="permanent"
      action-type="delete"
      title="Delete Customer?"
      :close="closePermanentModal"
      :action="deleteCustomerPermanently"
      action-text="Delete Permanently"
      :content="`<p class='mb-2'>${$t('Are you sure you want to delete the record permanently?')}</p>
        <p>${$t('Once deleted, all of its resources and data will be permanently deleted.')}</p>`"
    />

    <!-- Delete Account Confirmation Modal -->
    <Dialog
      max-width="md"
      :show="confirm"
      :close="closeModal"
      :action="deleteCustomer"
      action-type="delete"
      title="Delete Customer?"
      action-text="Delete Customer"
      :content="$t('Are you sure you want to delete the record?')"
    />

    <!-- Restore Account Confirmation Modal -->
    <Dialog
      max-width="md"
      :show="restoreConf"
      :action="restoreCustomer"
      title="Restore Customer!"
      :close="closeRestoreModal"
      action-text="Restore Customer"
      :content="$t('Are you sure you want to restore the record?')"
    />
  </admin-layout>
</template>

<script>
import Dialog from '@/Shared/Dialog.vue';
import TextInput from '@/Shared/TextInput.vue';
import SelectInput from '@/Shared/SelectInput.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import LoadingButton from '@/Shared/LoadingButton.vue';
import TextareaInput from '@/Shared/TextareaInput.vue';
import TecDialogModal from '@/Jetstream/DialogModal.vue';
import TrashedMessage from '@/Shared/TrashedMessage.vue';
import TecFormSection from '@/Jetstream/FormSection.vue';
import TecDangerButton from '@/Jetstream/DangerButton.vue';
import TecSectionTitle from '@/Jetstream/SectionTitle.vue';
import TecActionMessage from '@/Jetstream/ActionMessage.vue';
import TecSecondaryButton from '@/Jetstream/SecondaryButton.vue';

export default {
  remember: 'form',

   props: {
    edit: {
      // Define the props received from the controller
      type: Object,
      required: true,
    },
    items: {
      // Define the items prop received from the controller
      type: Array,
      required: true,
    },
  },

  components: {
    Dialog,
    TextInput,
    AdminLayout,
    SelectInput,
    LoadingButton,
    TextareaInput,
    TecDialogModal,
    TecFormSection,
    TecDangerButton,
    TrashedMessage,
    TecSectionTitle,
    TecActionMessage,
    TecSecondaryButton,
  },

  data() {
    return {
      confirm: false,
      permanent: false,
      restoreConf: false,
      form: this.$inertia.form({
        _method: this.edit ? 'put' : 'post',
        name: this.edit ? this.edit.name : null,
        email: this.edit ? this.edit.email : null,
        phone: this.edit ? this.edit.phone : null,
        details: this.edit ? this.edit.details : null,
        isp: this.edit ? this.edit.isp : null,
        ispId: this.edit ? this.edit.isp : null,
        customerId: this.edit ? this.edit.customerId : null,
      }),
       ispId: null,
    };
  },

  methods: {
    update() {
      this.form.post(this.edit ? this.route('customers.update', this.edit.id) : this.route('customers.store'), {
        preserveScroll: true,
        onSuccess: () => this.form.reset('password', 'photo'),
      });
    },
    destroy() {
      this.confirm = true;
    },
    deleteCustomer() {
      this.form.delete(route('customers.destroy', this.edit.id), {
        onSuccess: () => this.closeModal(),
      });
    },
    closeModal() {
      this.confirm = false;
    },
    restore() {
      this.restoreConf = true;
    },
    restoreCustomer() {
      this.$inertia.put(this.route('customers.restore', this.edit.id), {
        onSuccess: () => (this.restoreConf = false),
      });
    },
    closeRestoreModal() {
      this.restoreConf = false;
    },
    deletePermanently() {
      this.permanent = true;
    },
    deleteCustomerPermanently() {
      this.form.delete(route('customers.destroy.permanently', this.edit.id), {
        onSuccess: () => this.closeModal(),
      });
    },
    closePermanentModal() {
      this.permanent = false;
    },
  },
};
</script>
