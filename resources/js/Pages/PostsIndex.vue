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
    <div
        class="min-h-screen bg-gradient-to-br from-purple-50 via-pink-50 to-blue-50 py-8 px-4"
    >
        <div class="max-w-5xl mx-auto">
            <!-- Header -->
            <div class="text-center mb-8">
                <h1
                    class="text-4xl font-bold bg-gradient-to-r from-purple-600 to-pink-600 bg-clip-text text-transparent mb-2"
                >
                    Posts
                </h1>
                <p class="text-gray-600">Share your thoughts with the world</p>
            </div>

            <!-- Create Form -->
            <form
                @submit.prevent="submit"
                class="bg-white/80 backdrop-blur-sm rounded-3xl shadow-xl p-6 mb-8 border border-purple-100"
            >
                <input
                    v-model="title"
                    placeholder="Post title..."
                    class="w-full px-4 py-3 border-2 border-purple-100 rounded-xl mb-3 focus:border-purple-400 focus:ring-2 focus:ring-purple-100 transition outline-none"
                />
                <div v-if="errors.title" class="text-red-500 text-sm mb-3 px-1">
                    {{ errors.title[0] }}
                </div>

                <textarea
                    v-model="body"
                    placeholder="What's on your mind?"
                    rows="4"
                    class="w-full px-4 py-3 border-2 border-purple-100 rounded-xl mb-4 focus:border-purple-400 focus:ring-2 focus:ring-purple-100 transition outline-none resize-none"
                ></textarea>
                <div
                    v-if="errors.content"
                    class="text-red-500 text-sm mb-3 px-1"
                >
                    {{ errors.content[0] }}
                </div>

                <div class="flex items-center justify-between">
                    <button
                        :disabled="isSubmitting"
                        type="submit"
                        class="px-6 py-3 bg-gradient-to-r from-purple-600 to-pink-600 text-white rounded-xl font-semibold shadow-lg hover:shadow-xl disabled:opacity-50 transition-all"
                    >
                        <span v-if="!isSubmitting"> Publish</span>
                        <span v-else>Publishing...</span>
                    </button>
                    <div
                        v-if="successMessage"
                        class="text-green-600 font-medium"
                    >
                        ✓ {{ successMessage }}
                    </div>
                    <div v-if="errors.general" class="text-red-500 text-sm">
                        {{ errors.general[0] }}
                    </div>
                </div>
            </form>

            <!-- Posts Grid -->
            <div
                v-if="posts.length === 0"
                class="text-center text-gray-400 py-16"
            >
                <div class="text-6xl mb-4">📝</div>
                <p>No posts yet. Be the first to share!</p>
            </div>

            <div v-else class="grid gap-6 md:grid-cols-2">
                <article
                    v-for="post in posts"
                    :key="post.id"
                    class="group bg-white/90 backdrop-blur-sm rounded-2xl shadow-lg hover:shadow-2xl transition-all duration-300 overflow-hidden border border-purple-100 hover:border-purple-300"
                >
                    <div class="p-6">
                        <div class="flex items-start gap-3 mb-4">
                            <div
                                class="w-10 h-10 rounded-full bg-gradient-to-br from-purple-400 to-pink-400 flex items-center justify-center text-white font-bold shadow-md"
                            >
                                {{
                                    post.title
                                        ? post.title.charAt(0).toUpperCase()
                                        : "P"
                                }}
                            </div>
                            <div class="flex-1">
                                <h2
                                    class="text-xl font-bold text-gray-800 group-hover:text-purple-600 transition"
                                >
                                    {{ post.title }}
                                </h2>
                                <div class="text-xs text-gray-500 mt-1">
                                    {{ post.created_at || "Just now" }}
                                </div>
                            </div>
                        </div>
                        <p class="text-gray-700 leading-relaxed line-clamp-3">
                            {{ post.content }}
                        </p>
                    </div>
                    <div
                        class="px-6 py-3 bg-gradient-to-r from-purple-50 to-pink-50 border-t border-purple-100"
                    >
                        <a
                            href="#"
                            class="text-sm text-purple-600 hover:text-purple-800 font-medium"
                            >Read more →</a
                        >
                    </div>
                </article>
            </div>
        </div>
    </div>
</template>
