<script setup>
import { ref } from "vue";
import axios from "axios";

const props = defineProps({
    posts: {
        type: Array,
        default: () => [],
    },
});

const posts = ref([...props.posts]);
const title = ref("");
const body = ref("");
const errors = ref({});
const isSubmitting = ref(false);
const successMessage = ref("");

async function submit() {
    errors.value = {};
    successMessage.value = "";
    isSubmitting.value = true;
    try {
        const response = await axios.post("/posts", {
            title: title.value,
            content: body.value,
        });

        if (response.data && response.data.post) {
            posts.value.unshift(response.data.post);
        }

        title.value = "";
        body.value = "";
        successMessage.value = "Post created successfully.";

        setTimeout(() => (successMessage.value = ""), 3000);
    } catch (e) {
        if (e.response && e.response.status === 422) {
            errors.value = e.response.data.errors || {};
        } else {
            console.error(e);
            errors.value = { general: ["An unexpected error occurred."] };
        }
    } finally {
        isSubmitting.value = false;
    }
}
</script>

<template>
    <div class="max-w-3xl mx-auto p-4">
        <h1 class="text-2xl font-bold mb-4">Posts</h1>

        <form @submit.prevent="submit" class="mb-6">
            <div class="mb-2">
                <input
                    v-model="title"
                    placeholder="Title"
                    class="w-full p-2 border rounded"
                />
                <div v-if="errors.title" class="text-red-600 text-sm mt-1">
                    {{ errors.title[0] }}
                </div>
            </div>

            <div class="mb-2">
                <textarea
                    v-model="body"
                    placeholder="Body"
                    class="w-full p-2 border rounded"
                ></textarea>
                <div v-if="errors.content" class="text-red-600 text-sm mt-1">
                    {{ errors.content[0] }}
                </div>
            </div>

            <div class="flex items-center gap-3">
                <button
                    :disabled="isSubmitting"
                    type="submit"
                    class="px-4 py-2 bg-blue-600 text-white rounded disabled:opacity-50"
                >
                    <span v-if="!isSubmitting">Create</span>
                    <span v-else>Saving...</span>
                </button>

                <div v-if="successMessage" class="text-green-600">
                    {{ successMessage }}
                </div>
                <div v-if="errors.general" class="text-red-600">
                    {{ errors.general[0] }}
                </div>
            </div>
        </form>

        <ul>
            <li v-for="post in posts" :key="post.id" class="mb-4 border-b pb-2">
                <h2 class="font-semibold">{{ post.title }}</h2>
                <p class="text-gray-700">{{ post.content }}</p>
            </li>
        </ul>

        <div v-if="posts.length === 0" class="text-gray-500">No posts yet.</div>
    </div>
</template>
