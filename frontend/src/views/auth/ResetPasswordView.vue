<template>
  <div class="flex min-h-screen flex-col justify-center px-6 py-12 lg:px-8 bg-slate-50 text-slate-800">
    <div class="sm:mx-auto sm:w-full sm:max-w-sm">
      <div class="flex justify-center">
        <img src="/logo-dark.png" alt="Mamun Automobiles ERP" class="h-12 w-auto" />
      </div>
      <h2 class="mt-6 text-center text-2xl font-bold leading-9 tracking-tight text-slate-900">
        Create new password
      </h2>
      <p class="mt-2 text-center text-sm text-slate-500">
        Enter your new password below.
      </p>
    </div>

    <div class="mt-10 sm:mx-auto sm:w-full sm:max-w-sm bg-white p-8 rounded-lg shadow-lg border border-slate-200">
      
      <div v-if="success" class="rounded-md bg-green-50 p-4 border border-green-200 mb-6">
        <div class="flex">
          <div class="flex-shrink-0">
            <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
            </svg>
          </div>
          <div class="ml-3">
            <h3 class="text-sm font-medium text-green-800">{{ success }}</h3>
            <div class="mt-4">
              <router-link :to="{ name: 'login' }" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">
                Go to login &rarr;
              </router-link>
            </div>
          </div>
        </div>
      </div>

      <form v-else class="space-y-6" @submit.prevent="handleResetPassword">
        
        <div>
          <label for="email" class="block text-sm font-medium leading-6 text-slate-700">
            Email address
          </label>
          <div class="mt-2">
            <input
              id="email"
              v-model="email"
              name="email"
              type="email"
              required
              class="block w-full rounded-md border-0 bg-white py-1.5 px-3 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
            />
          </div>
        </div>

        <div>
          <label for="token" class="block text-sm font-medium leading-6 text-slate-700">
            Reset Token
          </label>
          <div class="mt-2">
            <input
              id="token"
              v-model="token"
              name="token"
              type="text"
              required
              class="block w-full rounded-md border-0 bg-white py-1.5 px-3 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
            />
          </div>
        </div>

        <div>
          <label for="password" class="block text-sm font-medium leading-6 text-slate-700">
            New Password
          </label>
          <div class="mt-2">
            <input
              id="password"
              v-model="password"
              name="password"
              type="password"
              required
              class="block w-full rounded-md border-0 bg-white py-1.5 px-3 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
            />
          </div>
        </div>

        <div>
          <label for="password_confirmation" class="block text-sm font-medium leading-6 text-slate-700">
            Confirm Password
          </label>
          <div class="mt-2">
            <input
              id="password_confirmation"
              v-model="password_confirmation"
              name="password_confirmation"
              type="password"
              required
              class="block w-full rounded-md border-0 bg-white py-1.5 px-3 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
            />
          </div>
        </div>

        <div v-if="error" class="rounded-md bg-red-50 p-4 border border-red-200">
          <div class="flex">
            <div class="flex-shrink-0">
              <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" />
              </svg>
            </div>
            <div class="ml-3">
              <h3 class="text-sm font-medium text-red-800">{{ error }}</h3>
            </div>
          </div>
        </div>

        <div>
          <button 
            type="submit" 
            class="flex w-full justify-center rounded-md bg-indigo-600 px-3 py-1.5 text-sm font-semibold leading-6 text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 transition"
            :disabled="loading"
          >
            {{ loading ? 'Resetting...' : 'Reset Password' }}
          </button>
        </div>
      </form>
      
      <div class="mt-6 text-center" v-if="!success">
        <router-link :to="{ name: 'login' }" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">
          &larr; Back to login
        </router-link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import api from '../../services/api';

const route = useRoute();

const email = ref('');
const token = ref('');
const password = ref('');
const password_confirmation = ref('');

const loading = ref(false);
const error = ref('');
const success = ref('');

onMounted(() => {
  if (route.query.email) email.value = route.query.email;
  if (route.query.token) token.value = route.query.token;
});

const handleResetPassword = async () => {
  loading.value = true;
  error.value = '';
  success.value = '';
  
  if (password.value !== password_confirmation.value) {
    error.value = "Passwords do not match.";
    loading.value = false;
    return;
  }
  
  try {
    const payload = {
      email: email.value,
      token: token.value,
      password: password.value,
      password_confirmation: password_confirmation.value
    };
    
    const res = await api.post('/auth/reset-password', payload);
    success.value = res.data.message || 'Password has been successfully reset.';
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to reset password.';
  } finally {
    loading.value = false;
  }
};
</script>
