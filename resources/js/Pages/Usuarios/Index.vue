<script setup>
import { ref } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import WarRoomLayout from '../../Layouts/WarRoomLayout.vue';
import Badge from '../../Components/Badge.vue';
import { Users, UserPlus, Shield, Edit2, Trash2, CheckCircle, X, ShieldAlert } from '@lucide/vue';

const props = defineProps({
  usuarios: {
    type: Array,
    default: () => [],
  },
  roles_disponibles: {
    type: Array,
    default: () => [],
  }
});

const isModalOpen = ref(false);
const isEditing = ref(false);
const editingUserId = ref(null);

const form = useForm({
  name: '',
  email: '',
  password: '',
  role: 'consultor',
});

const openCreateModal = () => {
  isEditing.value = false;
  editingUserId.value = null;
  form.reset();
  form.clearErrors();
  isModalOpen.value = true;
};

const openEditModal = (user) => {
  isEditing.value = true;
  editingUserId.value = user.id;
  form.name = user.name;
  form.email = user.email;
  form.role = user.role;
  form.password = '';
  form.clearErrors();
  isModalOpen.value = true;
};

const closeModal = () => {
  isModalOpen.value = false;
  form.reset();
};

const submitForm = () => {
  if (isEditing.value) {
    form.put(`/usuarios/${editingUserId.value}`, {
      onSuccess: () => closeModal(),
    });
  } else {
    form.post('/usuarios', {
      onSuccess: () => closeModal(),
    });
  }
};

const deleteUser = (user) => {
  if (confirm(`¿Estás seguro de eliminar al usuario ${user.name}?`)) {
    router.delete(`/usuarios/${user.id}`);
  }
};
</script>

<template>
  <Head title="Gestión de Usuarios & Roles" />

  <WarRoomLayout>
    <!-- Header -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
      <div>
        <div class="flex items-center gap-2">
          <Shield class="w-6 h-6 text-cyan-500" />
          <h1 class="text-2xl font-extrabold text-slate-900 dark:text-slate-100 tracking-tight">
            Usuarios & Matriz de Permisos
          </h1>
        </div>
        <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">
          Administra las cuentas de acceso y sus niveles de autorización operativa en la plataforma.
        </p>
      </div>

      <button
        type="button"
        @click="openCreateModal"
        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-bold text-sm transition-all shadow-md shadow-cyan-500/20"
      >
        <UserPlus class="w-4 h-4" />
        <span>Nuevo Usuario</span>
      </button>
    </div>

    <!-- Roles Matrix Explanatory Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <div
        v-for="rol in roles_disponibles"
        :key="rol.key"
        class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs"
      >
        <div class="flex items-center justify-between">
          <Badge variant="rol" :value="rol.key" size="md" />
          <span class="text-[10px] font-mono text-slate-400 uppercase tracking-widest">Nivel</span>
        </div>
        <p class="text-xs text-slate-600 dark:text-slate-400 mt-2.5 leading-relaxed">
          {{ rol.desc }}
        </p>
      </div>
    </div>

    <!-- Users Table -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden shadow-xs">
      <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
          <thead class="bg-slate-50 dark:bg-slate-950/70 border-b border-slate-200 dark:border-slate-800 text-xs uppercase tracking-wider text-slate-500 dark:text-slate-400 font-mono">
            <tr>
              <th class="px-6 py-4">Usuario</th>
              <th class="px-6 py-4">Correo Electrónico</th>
              <th class="px-6 py-4">Rol Asignado</th>
              <th class="px-6 py-4">Fecha de Alta</th>
              <th class="px-6 py-4 text-right">Acciones</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 font-sans">
            <tr
              v-for="u in usuarios"
              :key="u.id"
              class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition-colors"
            >
              <td class="px-6 py-4 font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-full bg-slate-200 dark:bg-slate-800 flex items-center justify-center text-xs font-bold font-mono">
                  {{ u.name.charAt(0) }}
                </div>
                <div>
                  <span>{{ u.name }}</span>
                  <span v-if="u.is_current" class="ml-2 text-[10px] px-1.5 py-0.2 rounded-sm bg-cyan-500/20 text-cyan-600 dark:text-cyan-400 font-mono font-normal">
                    (Tú)
                  </span>
                </div>
              </td>
              <td class="px-6 py-4 text-slate-600 dark:text-slate-400 font-mono text-xs">
                {{ u.email }}
              </td>
              <td class="px-6 py-4">
                <Badge variant="rol" :value="u.role" size="sm" />
              </td>
              <td class="px-6 py-4 text-slate-500 text-xs">
                {{ u.created_at || 'Reciente' }}
              </td>
              <td class="px-6 py-4 text-right space-x-1">
                <button
                  type="button"
                  @click="openEditModal(u)"
                  class="p-1.5 rounded-lg text-slate-500 hover:text-cyan-600 hover:bg-cyan-500/10 dark:hover:bg-cyan-500/20 transition-colors"
                  title="Editar Usuario"
                >
                  <Edit2 class="w-4 h-4" />
                </button>
                <button
                  v-if="!u.is_current"
                  type="button"
                  @click="deleteUser(u)"
                  class="p-1.5 rounded-lg text-slate-500 hover:text-rose-600 hover:bg-rose-500/10 dark:hover:bg-rose-500/20 transition-colors"
                  title="Eliminar Usuario"
                >
                  <Trash2 class="w-4 h-4" />
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Create / Edit User Modal -->
    <div
      v-if="isModalOpen"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-xs"
    >
      <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 sm:p-8 max-w-lg w-full shadow-2xl relative">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800">
          <h3 class="text-lg font-bold text-slate-900 dark:text-slate-100">
            {{ isEditing ? 'Editar Usuario' : 'Crear Nuevo Usuario' }}
          </h3>
          <button @click="closeModal" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
            <X class="w-5 h-5" />
          </button>
        </div>

        <form @submit.prevent="submitForm" class="mt-4 space-y-4">
          <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1">
              Nombre Completo
            </label>
            <input
              v-model="form.name"
              type="text"
              required
              class="w-full px-3.5 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-slate-100 text-sm focus:ring-2 focus:ring-cyan-500"
            />
            <p v-if="form.errors.name" class="text-xs text-rose-500 mt-1">{{ form.errors.name }}</p>
          </div>

          <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1">
              Correo Electrónico
            </label>
            <input
              v-model="form.email"
              type="email"
              required
              class="w-full px-3.5 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-slate-100 text-sm focus:ring-2 focus:ring-cyan-500"
            />
            <p v-if="form.errors.email" class="text-xs text-rose-500 mt-1">{{ form.errors.email }}</p>
          </div>

          <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1">
              Rol en la Plataforma
            </label>
            <select
              v-model="form.role"
              required
              class="w-full px-3.5 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-slate-100 text-sm focus:ring-2 focus:ring-cyan-500"
            >
              <option value="admin">Administrador (Control Total)</option>
              <option value="consultor">Consultor Estratégico (Operativo y Carga)</option>
              <option value="visualizador">Visualizador Ejecutivo (Solo Lectura)</option>
            </select>
          </div>

          <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1">
              {{ isEditing ? 'Nueva Contraseña (dejar en blanco para conservar)' : 'Contraseña' }}
            </label>
            <input
              v-model="form.password"
              type="password"
              :required="!isEditing"
              placeholder="••••••••"
              class="w-full px-3.5 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-slate-100 text-sm focus:ring-2 focus:ring-cyan-500"
            />
            <p v-if="form.errors.password" class="text-xs text-rose-500 mt-1">{{ form.errors.password }}</p>
          </div>

          <div class="pt-4 flex items-center justify-end gap-2">
            <button
              type="button"
              @click="closeModal"
              class="px-4 py-2 rounded-xl text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 text-sm"
            >
              Cancelar
            </button>
            <button
              type="submit"
              :disabled="form.processing"
              class="px-5 py-2 rounded-xl bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-bold text-sm shadow-md"
            >
              {{ isEditing ? 'Guardar Cambios' : 'Crear Usuario' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </WarRoomLayout>
</template>
