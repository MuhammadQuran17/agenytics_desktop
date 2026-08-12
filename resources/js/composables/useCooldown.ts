import { ref, onUnmounted } from 'vue';

export function useCooldown(initialDuration: number) {
    const cooldownTime = ref(0);
    let intervalId: ReturnType<typeof setInterval> | null = null;

    const startCooldown = (duration: number) => {
        cooldownTime.value = duration;

        if (intervalId !== null) {
            clearInterval(intervalId);
        }

        intervalId = setInterval(() => {
            cooldownTime.value--;

            if (cooldownTime.value <= 0) {
                cooldownTime.value = 0;
                if (intervalId !== null) {
                    clearInterval(intervalId);
                    intervalId = null;
                }
            }
        }, 1000);
    };

    onUnmounted(() => {
        if (intervalId !== null) {
            clearInterval(intervalId);
        }
    });

    return {
        cooldownTime,
        startCooldown,
    };
}

