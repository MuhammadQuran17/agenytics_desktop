<script setup lang="ts">
import { onMounted } from 'vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';
import { useCooldown } from '@/composables/useCooldown';

defineProps<{
    status?: string;
}>();

const form = useForm({});

const COOLDOWN_DURATION = 50;
const { cooldownTime, startCooldown } = useCooldown(COOLDOWN_DURATION);

onMounted(() => {
    startCooldown(COOLDOWN_DURATION);
});

const submit = () => {
    if (cooldownTime.value > 0) return;

    form.post(route('verification.send'), {
        onSuccess: () => {
            startCooldown(COOLDOWN_DURATION);
        },
    });
};
</script>

<template>
    <AuthLayout title="Verify email" description="Please verify your email address by clicking on the link we just emailed to you.">
        <Head title="Email verification" />

        <div v-if="status === 'verification-link-sent'" class="mb-4 text-center text-sm font-medium text-green-600">
            A new verification link has been sent to the email address you provided during registration.
        </div>

        <form @submit.prevent="submit" class="space-y-6 text-center">
            <div class="flex items-center justify-center gap-3">
                <Button 
                    :disabled="cooldownTime > 0 || form.processing" 
                    variant="secondary"
                    :class="{ 'opacity-50': cooldownTime > 0 || form.processing }"
                >
                    <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin" />
                    Resend verification email
                </Button>

                <div v-show="cooldownTime > 0" class="flex items-center justify-center w-16 h-10 text-sm font-semibold text-muted-foreground bg-muted rounded-md">
                    {{ cooldownTime }}s
                </div>
            </div>

            <TextLink :href="route('logout')" method="post" as="button" class="mx-auto block text-sm"> Log out </TextLink>
        </form>
    </AuthLayout>
</template>
