<script setup lang="ts">
import Input from "@/Components/ui/input/Input.vue";
import { Search } from "lucide-vue-next";

const model = defineModel<string>();
</script>

<template>
    <div class="relative items-center w-full md:max-w-52">
        <Input
            v-model="model"
            id="search"
            type="text"
            placeholder="Search..."
            class="pr-10"
        />
        <span
            class="absolute inset-y-0 flex items-center justify-center px-2 end-1"
        >
            <Search class="size-4 text-neutral-500 text-muted-foreground" />
        </span>
    </div>
</template>
