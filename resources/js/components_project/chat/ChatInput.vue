<script setup lang="ts">
import { ref } from 'vue';
import { ArrowUp, Paperclip, X } from 'lucide-vue-next';
import { Textarea } from "@/components/ui/textarea";
import { Button } from "@/components/ui/button";
import ChatSuggestions from './ChatSuggestions.vue';

const MAX_PDFS = 4;

const emit = defineEmits<{
    (e: 'send', message: string, pdfs: File[]): void;
}>();

const newMessage = ref('');
const attachedPdfs = ref<File[]>([]);
const fileInput = ref<HTMLInputElement | null>(null);

const openFilePicker = () => {
    fileInput.value?.click();
};

const handleFilesSelected = (event: Event) => {
    const input = event.target as HTMLInputElement;
    const pdfFiles = Array.from(input.files ?? []).filter((f) => f.type === 'application/pdf');
    attachedPdfs.value = [...attachedPdfs.value, ...pdfFiles].slice(0, MAX_PDFS);
    input.value = '';
};

const removePdf = (index: number) => {
    attachedPdfs.value = attachedPdfs.value.filter((_, i) => i !== index);
};

const sendMessage = () => {
    if (!newMessage.value && attachedPdfs.value.length === 0) return;

    // The backend requires a non-empty message; fall back to a sensible
    // default when the user only attached PDFs without typing anything.
    const message = newMessage.value || 'Process the attached PDF(s).';

    emit('send', message, attachedPdfs.value);
    newMessage.value = '';
    attachedPdfs.value = [];
};

const handleSuggestionSelect = (message: string) => {
    newMessage.value = message;
    sendMessage();
};

const handleKeyDown = (event: KeyboardEvent) => {
    if (event.key === 'Enter' && !event.shiftKey) {
        event.preventDefault();
        sendMessage();
    }
};

// Lets a parent (e.g. clicking "Edit" on a past message) load text into the
// box for the user to tweak and send, instead of editing the bubble in place.
const setDraft = (text: string) => {
    newMessage.value = text;
};

defineExpose({ setDraft });
</script>

<template>
    <div class="max-w-3xl sticky bottom-3 z-50 mx-auto w-full px-4 md:px-0">
        <ChatSuggestions @select="handleSuggestionSelect" />

        <div v-if="attachedPdfs.length" class="flex flex-wrap gap-2 mb-2">
            <div
                v-for="(pdf, index) in attachedPdfs"
                :key="index"
                class="flex items-center gap-1.5 bg-muted rounded-full py-1 pl-3 pr-1.5 text-xs"
            >
                <span class="max-w-[160px] truncate">{{ pdf.name }}</span>
                <button type="button" @click="removePdf(index)" class="rounded-full hover:bg-background/60 p-0.5 cursor-pointer">
                    <X class="h-3 w-3" />
                </button>
            </div>
        </div>

        <div class="relative">
            <input
                ref="fileInput"
                type="file"
                accept="application/pdf"
                multiple
                class="hidden"
                @change="handleFilesSelected"
            />

            <Textarea
                data-testid="multimodal-input"
                v-model.trim="newMessage"
                :class="[
                    'min-h-[24px] max-h-[calc(75dvh)] overflow-hidden resize-none',
                    'rounded-3xl bg-background pb-14 pt-4 pl-14 pr-5 dark:border-zinc-700 w-full',
                    'border-2 focus:outline-none focus:ring-0'
                ]"
                placeholder="Send a message..."
                @keydown="handleKeyDown"
            />

            <!-- Attach PDF Button -->
            <div class="absolute bottom-2 left-1 px-2">
                <Button
                    type="button"
                    variant="ghost"
                    @click="openFilePicker"
                    :disabled="attachedPdfs.length >= MAX_PDFS"
                    class="rounded-[50%] p-1 cursor-pointer"
                >
                    <Paperclip class="h-5 w-5"/>
                </Button>
            </div>

            <!-- Send Message Button -->
            <div class="absolute bottom-2 right-1 px-2">
                <Button @click="sendMessage" :disabled="!newMessage && !attachedPdfs.length" class="rounded-[50%] p-1 cursor-pointer">
                    <ArrowUp class="h-5 w-5"/>
                </Button>
            </div>
        </div>
    </div>
</template>
