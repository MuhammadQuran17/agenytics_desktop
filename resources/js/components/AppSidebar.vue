<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Button } from '@/components/ui/button';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { useAiChatStore } from '@/store/aiChatStore';
import { Link, usePage } from '@inertiajs/vue3';
import { Database, Key, MessageCircle, Plus } from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';
import { route } from 'ziggy-js';
import { computed } from 'vue';
import { type NavItem } from '@/types';
import { type SharedData } from '@/types';

const page = usePage<SharedData>();

// [START] Chats

const mainNavItems: NavItem[] = [];

if(page.props.userChats.length > 0) {
    for(const chat of page.props.userChats) {
        const formattedChatCreatedDate = (() => {
            const date = new Date(chat.created_at);
            const month = date.toLocaleString('en-US', { month: 'short', timeZone: 'UTC' });
            const day = date.getUTCDate();
            const hours = String(date.getHours()).padStart(2, '0');
            const minutes = String(date.getMinutes()).padStart(2, '0');
            return `${month} ${day}, ${hours}:${minutes} UTC`;
        })();

        mainNavItems.push({
            title: formattedChatCreatedDate,
            href: route('chat.index', {session_id: chat.session_id}, false),
            icon: MessageCircle,
            sessionId: chat.session_id,
        });
    }
} else{
    mainNavItems.push({
        title: 'Chat',
        href: route('chat.index'),
        icon: MessageCircle,
    });
}
// [END] Chats


const footerNavItems: NavItem[] = [];

const hasResponseFromAi = computed(() => useAiChatStore().currentChatHistory.length > 0);

</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="route('dashboard')">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
            
            <!-- New Chat Button - Only visible if there's an active chat with history -->
            <SidebarMenu v-if="hasResponseFromAi" class="py-2">
                <SidebarMenuItem>
                    <SidebarMenuButton as-child class="w-full justify-start gap-2 hover:bg-primary hover:text-primary-foreground cursor-pointer bg-white border border-border shadow-sm dark:text-black">
                        <Link :href="route('chat.create')" class="w-full">
                            <Plus class="h-4 w-4" />
                            <span>New Chat</span>
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
