<script setup>
import { ref } from "vue";
import axios from "axios";

const props = defineProps({
    posts: {
        type: Array,
        default: () => [],
    },
    pagination: {
        type: Object,
        default: () => ({
            current_page: 1,
            last_page: 1,
            per_page: 6,
            total: 0,
        }),
    },
    categories: {
        type: Array,
        default: () => [],
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
});

const posts = ref([...props.posts]);
const pagination = ref({ ...props.pagination });
const categories = ref([...props.categories]);
const filters = ref({ ...props.filters });
const searchQuery = ref(filters.value.search || "");
const selectedCategory = ref(filters.value.category || "");
const currentPage = ref(
    filters.value.page || pagination.value.current_page || 1
);
const title = ref("");
const body = ref("");
const category = ref("");
const excerpt = ref("");
const tags = ref("");
const errors = ref({});
const isSubmitting = ref(false);
const successMessage = ref("");
const deletingId = ref(null);
const editingId = ref(null);

// Fetch posts with filters and pagination via AJAX
async function fetchPosts(page = 1) {
    try {
        const params = {
            search: searchQuery.value || undefined,
            category: selectedCategory.value || undefined,
            page,
        };

        const res = await axios.get("/posts", { params });
        if (res.data && res.data.posts) {
            posts.value = res.data.posts;
            if (res.data.meta) {
                pagination.value = { ...res.data.meta };
                currentPage.value = pagination.value.current_page;
            }
        }
    } catch (e) {
        console.error("Failed to fetch posts", e);
    }
}

function startEdit(post) {
    editingId.value = post.id;
    title.value = post.title || "";
    body.value = post.content || "";
    category.value = post.category || "";
    excerpt.value = post.excerpt || "";
    tags.value = post.tags || "";
    window.scrollTo({ top: 0, behavior: "smooth" });
}

function cancelEdit() {
    editingId.value = null;
    title.value = "";
    body.value = "";
    category.value = "";
    excerpt.value = "";
    tags.value = "";
    errors.value = {};
}

function applyFilters() {
    // reset to page 1 on new filter
    fetchPosts(1);
}

async function submit() {
    errors.value = {};
    successMessage.value = "";
    isSubmitting.value = true;

    const payload = {
        title: title.value,
        content: body.value,
        category: category.value,
        excerpt: excerpt.value,
        tags: tags.value,
        read_time: Math.max(
            1,
            Math.ceil(body.value.split(" ").filter(Boolean).length / 200)
        ),
    };

    try {
        if (editingId.value) {
            // Update existing post
            const response = await axios.put(
                `/posts/${editingId.value}`,
                payload
            );
            if (response.data && response.data.post) {
                const idx = posts.value.findIndex(
                    (p) => p.id === response.data.post.id
                );
                if (idx !== -1) posts.value.splice(idx, 1, response.data.post);
            }
            successMessage.value = "Post updated successfully.";
            cancelEdit();
        } else {
            // Create new post
            const response = await axios.post("/posts", payload);
            if (response.data && response.data.post) {
                posts.value.unshift(response.data.post);
            }
            successMessage.value = "Post created successfully.";
            title.value = "";
            body.value = "";
            category.value = "";
            excerpt.value = "";
            tags.value = "";
        }

        setTimeout(() => (successMessage.value = ""), 3000);
    } catch (e) {
        if (e.response && e.response.status === 422) {
            errors.value = e.response.data.errors || {};
        } else if (
            e.response &&
            (e.response.status === 403 || e.response.status === 401)
        ) {
            errors.value = {
                general: ["You are not authorized to perform this action."],
            };
        } else {
            console.error(e);
            errors.value = { general: ["An unexpected error occurred."] };
        }
    } finally {
        isSubmitting.value = false;
    }
}

async function removePost(post) {
    if (!confirm("Are you sure you want to delete this post?")) return;
    deletingId.value = post.id;
    try {
        const response = await axios.delete(`/posts/${post.id}`);
        if (response.status === 200) {
            const idx = posts.value.findIndex((p) => p.id === post.id);
            if (idx !== -1) posts.value.splice(idx, 1);

            // show success message briefly
            successMessage.value = "Post deleted successfully.";
            setTimeout(() => (successMessage.value = ""), 3000);
        }
    } catch (e) {
        if (
            e.response &&
            (e.response.status === 403 || e.response.status === 401)
        ) {
            errors.value = {
                general: ["You are not authorized to delete this post."],
            };
        } else {
            console.error(e);
            errors.value = {
                general: ["An error occurred while deleting the post."],
            };
        }
    } finally {
        deletingId.value = null;
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

                <div class="grid grid-cols-2 gap-3 mb-3">
                    <input
                        v-model="category"
                        placeholder="Category (e.g., Tech, Lifestyle)"
                        class="px-4 py-3 border-2 border-purple-100 rounded-xl focus:border-purple-400 focus:ring-2 focus:ring-purple-100 transition outline-none"
                    />
                    <input
                        v-model="tags"
                        placeholder="Tags (comma separated)"
                        class="px-4 py-3 border-2 border-purple-100 rounded-xl focus:border-purple-400 focus:ring-2 focus:ring-purple-100 transition outline-none"
                    />
                </div>

                <textarea
                    v-model="excerpt"
                    placeholder="Brief excerpt (optional)"
                    rows="2"
                    class="w-full px-4 py-3 border-2 border-purple-100 rounded-xl mb-3 focus:border-purple-400 focus:ring-2 focus:ring-purple-100 transition outline-none resize-none"
                ></textarea>

                <textarea
                    v-model="body"
                    placeholder="Write your post content..."
                    rows="5"
                    class="w-full px-4 py-3 border-2 border-purple-100 rounded-xl mb-4 focus:border-purple-400 focus:ring-2 focus:ring-purple-100 transition outline-none resize-none"
                ></textarea>
                <div
                    v-if="errors.content"
                    class="text-red-500 text-sm mb-3 px-1"
                >
                    {{ errors.content[0] }}
                </div>

                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <button
                            :disabled="isSubmitting"
                            type="submit"
                            class="px-6 py-3 bg-gradient-to-r from-purple-600 to-pink-600 text-white rounded-xl font-semibold shadow-lg hover:shadow-xl disabled:opacity-50 transition-all"
                        >
                            <span v-if="!isSubmitting && !editingId">
                                Publish</span
                            >
                            <span v-if="!isSubmitting && editingId"
                                >Update</span
                            >
                            <!-- <span v-else>Processing...</span> -->
                        </button>

                        <button
                            v-if="editingId"
                            type="button"
                            @click="cancelEdit"
                            class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg border border-gray-200 hover:bg-gray-50"
                        >
                            Cancel
                        </button>
                    </div>

                    <div class="flex items-center gap-4">
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
                </div>
            </form>

            <!-- Filters -->
            <div class="flex items-center gap-3 mb-6">
                <input
                    v-model="searchQuery"
                    placeholder="Search posts..."
                    class="px-4 py-2 border rounded-lg w-1/2"
                />
                <select
                    v-model="selectedCategory"
                    class="px-4 py-2 border rounded-lg"
                >
                    <option value="">All categories</option>
                    <option v-for="cat in categories" :key="cat" :value="cat">
                        {{ cat }}
                    </option>
                </select>
                <button
                    @click.prevent="applyFilters"
                    class="px-4 py-2 bg-purple-600 text-white rounded-lg"
                >
                    Filter
                </button>
            </div>

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
                        <!-- Header with Avatar and Meta -->
                        <div class="flex items-start gap-3 mb-3">
                            <div
                                class="w-12 h-12 rounded-full bg-gradient-to-br from-purple-400 to-pink-400 flex items-center justify-center text-white font-bold shadow-md"
                            >
                                {{
                                    post.title
                                        ? post.title.charAt(0).toUpperCase()
                                        : "P"
                                }}
                            </div>
                            <div class="flex-1">
                                <h2
                                    class="text-xl font-bold text-gray-800 group-hover:text-purple-600 transition mb-1"
                                >
                                    {{ post.title }}
                                </h2>
                                <div
                                    class="flex items-center gap-2 text-xs text-gray-500"
                                >
                                    <span>{{
                                        new Date(
                                            post.created_at
                                        ).toLocaleDateString() || "Just now"
                                    }}</span>
                                    <span v-if="post.read_time"
                                        >• {{ post.read_time }} min read</span
                                    >
                                    <span v-if="post.views"
                                        >• {{ post.views }} views</span
                                    >
                                </div>
                            </div>
                        </div>

                        <!-- Category Badge -->
                        <div v-if="post.category" class="mb-3">
                            <span
                                class="inline-block px-3 py-1 bg-purple-100 text-purple-700 rounded-full text-xs font-semibold"
                            >
                                {{ post.category }}
                            </span>
                        </div>

                        <!-- Excerpt or Content Preview -->
                        <p
                            class="text-gray-700 leading-relaxed mb-3 line-clamp-3"
                        >
                            {{ post.excerpt || post.content }}
                        </p>

                        <!-- Tags -->
                        <div v-if="post.tags" class="flex flex-wrap gap-2 mb-3">
                            <span
                                v-for="tag in post.tags.split(',').slice(0, 3)"
                                :key="tag"
                                class="text-xs px-2 py-1 bg-pink-50 text-pink-600 rounded-lg"
                            >
                                #{{ tag.trim() }}
                            </span>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div
                        class="px-6 py-3 bg-gradient-to-r from-purple-50 to-pink-50 border-t border-purple-100 flex items-center justify-between"
                    >
                        <a
                            href="#"
                            class="text-sm text-purple-600 hover:text-purple-800 font-medium"
                        >
                            Read more →
                        </a>
                        <div class="flex items-center gap-3">
                            <button
                                @click.prevent="startEdit(post)"
                                class="text-xs text-purple-600 hover:text-purple-800 font-medium px-3 py-1 rounded-lg border border-transparent hover:bg-purple-100"
                            >
                                Edit
                            </button>
                            <button
                                @click.prevent="removePost(post)"
                                :disabled="deletingId === post.id"
                                class="text-xs text-red-500 hover:text-red-700 font-medium px-3 py-1 rounded-lg border border-transparent hover:bg-red-50 disabled:opacity-50"
                            >
                                <span v-if="deletingId === post.id"
                                    >Deleting...</span
                                >
                                <span v-else>Delete</span>
                            </button>
                            <div class="text-xs text-gray-400">
                                Post #{{ post.id }}
                            </div>
                        </div>
                    </div>
                </article>
            </div>

            <!-- Pagination -->
            <div
                v-if="pagination.total > pagination.per_page"
                class="mt-8 flex items-center justify-center"
            >
                <div class="inline-flex gap-2">
                    <button
                        :disabled="currentPage === 1"
                        @click.prevent="fetchPosts(pagination.current_page - 1)"
                        class="px-3 py-1 bg-gray-100 rounded-lg"
                    >
                        Prev
                    </button>

                    <button
                        v-for="p in Math.min(7, pagination.last_page)
                            ? Array.from(
                                  { length: Math.min(7, pagination.last_page) },
                                  (_, i) => i + 1
                              )
                            : []"
                        :key="p"
                        @click.prevent="fetchPosts(p)"
                        :class="{
                            'bg-purple-600 text-white':
                                p === pagination.current_page,
                            'bg-gray-100': p !== pagination.current_page,
                        }"
                        class="px-3 py-1 rounded-lg"
                    >
                        {{ p }}
                    </button>

                    <button
                        :disabled="
                            pagination.current_page >= pagination.last_page
                        "
                        @click.prevent="fetchPosts(pagination.current_page + 1)"
                        class="px-3 py-1 bg-gray-100 rounded-lg"
                    >
                        Next
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
