<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { SidebarGroup, SidebarGroupLabel, SidebarMenu, SidebarMenuAction, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem, type SharedData } from '@/types';
import { Link, router, usePage } from '@inertiajs/vue3';
import { Trash2 } from 'lucide-vue-next';
import { ref } from 'vue';

defineProps<{
    items: NavItem[];
}>();

const page = usePage<SharedData>();

// Check if the current page is equal to item.href, otherwise activate because of /chat route
const isActive = (item: NavItem, index: number) => {
    return item.href === page.url || (page.url === '/chat' && index === 0);
};

const chatPendingDeletion = ref<NavItem | null>(null);
const isDeleting = ref(false);

const confirmDelete = (item: NavItem, event: Event) => {
    event.preventDefault();
    event.stopPropagation();
    chatPendingDeletion.value = item;
};

const deleteChat = () => {
    if (!chatPendingDeletion.value?.sessionId) {
        return;
    }

    isDeleting.value = true;

    router.delete(route('chat.destroy', chatPendingDeletion.value.sessionId), {
        preserveScroll: true,
        onFinish: () => {
            isDeleting.value = false;
            chatPendingDeletion.value = null;
        },
    });
};
</script>

<template>
    <SidebarGroup class="px-2 py-0">
        <SidebarGroupLabel>Platform</SidebarGroupLabel>
        <SidebarMenu>
            <SidebarMenuItem v-for="item in items" :key="item.title">
                <SidebarMenuButton
                    as-child
                    :is-active="isActive(item, items.indexOf(item))"
                    :tooltip="item.title"
                >
                    <Link :href="item.href">
                        <component :is="item.icon" />
                        <span>{{ item.title }}</span>
                    </Link>
                </SidebarMenuButton>

                <SidebarMenuAction v-if="item.sessionId" show-on-hover @click="confirmDelete(item, $event)">
                    <Trash2 />
                </SidebarMenuAction>
            </SidebarMenuItem>
        </SidebarMenu>
    </SidebarGroup>

    <Dialog :open="!!chatPendingDeletion" @update:open="(open) => { if (!open) chatPendingDeletion = null; }">
        <DialogContent>
            <DialogHeader>
                <DialogTitle>Delete chat?</DialogTitle>
                <DialogDescription>
                    This will permanently delete "{{ chatPendingDeletion?.title }}" and all of its messages. This action cannot be undone.
                </DialogDescription>
            </DialogHeader>
            <DialogFooter class="gap-2">
                <Button variant="secondary" @click="chatPendingDeletion = null">Cancel</Button>
                <Button variant="destructive" :disabled="isDeleting" @click="deleteChat">Delete</Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
