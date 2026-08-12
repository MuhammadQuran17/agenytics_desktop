<script setup lang="ts">
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';

const emit = defineEmits(['delete']);
const isOpen = ref(false);

const handleDelete = () => {
    emit('delete');
    isOpen.value = false;
};

</script>

<template>
    <Dialog v-model:open="isOpen">
        <DialogTrigger as-child>
            <slot name="trigger"></slot>
        </DialogTrigger>
        <DialogContent>
            <DialogHeader class="space-y-3">
                <DialogTitle>Are you sure you want to delete?</DialogTitle>
                <DialogDescription>
                    Once it is deleted, you can not restore it.
                </DialogDescription>
            </DialogHeader>

            <DialogFooter class="gap-2">
                <DialogClose as-child>
                    <Button variant="secondary">Cancel</Button>
                </DialogClose>
                <Button variant="destructive" @click="handleDelete">
                    Delete
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>