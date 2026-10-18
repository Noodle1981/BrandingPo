import { watch, onUnmounted } from 'vue';

/**
 * Registra un listener global de teclado (por defecto tecla Escape) mientras un modal esté abierto,
 * garantizando la limpieza estricta en el ciclo de vida onUnmounted para prevenir fugas de memoria.
 *
 * @param {import('vue').Ref<boolean> | (() => boolean)} isOpen - Ref o función getter que indica si el modal está abierto
 * @param {() => void} onClose - Callback a invocar al presionar Escape
 */
export function useModalKeydown(isOpen, onClose) {
  const handleKeyDown = (e) => {
    const openVal = typeof isOpen === 'function' ? isOpen() : isOpen.value;
    if (e.key === 'Escape' && openVal) {
      onClose();
    }
  };

  watch(
    () => (typeof isOpen === 'function' ? isOpen() : isOpen.value),
    (newVal) => {
      if (newVal) {
        window.addEventListener('keydown', handleKeyDown);
      } else {
        window.removeEventListener('keydown', handleKeyDown);
      }
    },
    { immediate: true }
  );

  onUnmounted(() => {
    window.removeEventListener('keydown', handleKeyDown);
  });
}
