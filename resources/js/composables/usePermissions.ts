import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

interface User {
  id: number;
  name: string;
  email: string;
  [key: string]: any;
}

interface AuthData {
  user: User | null;
  roles: string[];
  permissions: string[];
  can: {
    isAdmin: boolean;
    isProfessor: boolean;
    isStudent: boolean;
  };
}

export function usePermissions() {
  const page = usePage();

  const auth = computed(() => page.props.auth as unknown as AuthData);

  /**
   * Verificar si el usuario tiene un permiso específico
   */
  const can = (permission: string): boolean => {
    return auth.value?.permissions?.includes(permission) ?? false;
  };

  /**
   * Verificar si el usuario tiene cualquiera de los permisos
   */
  const canAny = (permissions: string[]): boolean => {
    return permissions.some(permission => can(permission));
  };

  /**
   * Verificar si el usuario tiene todos los permisos
   */
  const canAll = (permissions: string[]): boolean => {
    return permissions.every(permission => can(permission));
  };

  /**
   * Verificar si el usuario tiene un rol específico
   */
  const hasRole = (role: string): boolean => {
    return auth.value?.roles?.includes(role) ?? false;
  };

  /**
   * Verificar si el usuario tiene cualquiera de los roles
   */
  const hasAnyRole = (roles: string[]): boolean => {
    return roles.some(role => hasRole(role));
  };

  /**
   * Verificar si el usuario tiene todos los roles
   */
  const hasAllRoles = (roles: string[]): boolean => {
    return roles.every(role => hasRole(role));
  };

  // Helpers para roles específicos
  const isAdmin = computed(() => auth.value?.can?.isAdmin ?? false);
  const isProfessor = computed(() => auth.value?.can?.isProfessor ?? false);
  const isStudent = computed(() => auth.value?.can?.isStudent ?? false);

  // Usuario autenticado
  const user = computed(() => auth.value?.user ?? null);
  const isAuthenticated = computed(() => !!auth.value?.user);

  return {
    // Auth data
    auth,
    user,
    isAuthenticated,

    // Permission checks
    can,
    canAny,
    canAll,

    // Role checks
    hasRole,
    hasAnyRole,
    hasAllRoles,

    // Role helpers
    isAdmin,
    isProfessor,
    isStudent,
  };
}

