<script setup lang="ts">
import { reactive, watch } from 'vue'
import type { Customer, CustomerPayload } from '../../types/customer'
import BaseButton from '../ui/BaseButton.vue'
import BaseInput from '../ui/BaseInput.vue'
import BaseModal from '../ui/BaseModal.vue'
const props=defineProps<{ customer: Customer | null }>(); const emit=defineEmits<{ close: []; submit: [payload: CustomerPayload] }>(); const form=reactive({name:'',phone:'',address:'',is_active:true})
watch(()=>props.customer,(customer)=>Object.assign(form,customer?{...customer,phone:customer.phone??'',address:customer.address??''}:{name:'',phone:'',address:'',is_active:true}),{immediate:true})
function submit(){emit('submit',{...form,phone:form.phone||null,address:form.address||null})}
</script>
<template><BaseModal :title="customer?'Edit customer':'Add customer'" @close="emit('close')"><h2>{{customer?'Edit customer':'Add customer'}}</h2><form @submit.prevent="submit"><BaseInput v-model="form.name" label="Name" required/><BaseInput v-model="form.phone" label="Phone"/><label>Address<textarea v-model="form.address" rows="3"/></label><label class="check"><input v-model="form.is_active" type="checkbox">Active customer</label><BaseButton type="submit">Save customer</BaseButton></form></BaseModal></template>
