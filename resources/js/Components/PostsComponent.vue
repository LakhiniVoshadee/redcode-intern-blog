<template>
    <div>
        <!-- Simple Create Form -->
        <form @submit.prevent="createPost" class="mb-4">
            <input
                v-model="newPost.title"
                placeholder="Title"
                required
                class="block w-full mb-2"
            />
            <input
                v-model="newPost.category"
                placeholder="Category"
                class="block w-full mb-2"
            />
            <input
                v-model="newPost.tags"
                placeholder="Tags (comma separated)"
                class="block w-full mb-2"
            />
            <textarea
                v-model="newPost.content"
                placeholder="Content"
                required
                class="block w-full mb-2"
            ></textarea>
            <div style="display: flex; gap: 12px; align-items: center">
                <button
                    type="submit"
                    :disabled="isSubmitting"
                    style="padding: 6px 12px"
                >
                    <span v-if="isSubmitting">Processing...</span>
                    <span v-else-if="editingId">Update</span>
                    <span v-else>Add Post</span>
                </button>
                <div v-if="successMessage" style="color: green">
                    ✓ {{ successMessage }}
                </div>
            </div>
        </form>

        <!-- Display List -->
        <ul>
            <li
                v-for="post in posts"
                :key="post.id"
                style="
                    margin-bottom: 1rem;
                    border-bottom: 1px solid #eee;
                    padding-bottom: 1rem;
                "
            >
                <h3>{{ post.title }}</h3>
                <div
                    v-if="post.category"
                    style="font-size: 0.85rem; color: #6b21a8"
                >
                    Category: {{ post.category }}
                </div>
                <div style="font-size: 0.9rem; color: #374151">
                    {{ post.excerpt || post.content }}
                </div>
                <div v-if="post.tags" style="margin-top: 6px">
                    <span
                        v-for="tag in post.tags.split(',').slice(0, 3)"
                        :key="tag"
                        style="margin-right: 6px"
                        >#{{ tag.trim() }}</span
                    >
                </div>

                <div
                    style="
                        margin-top: 8px;
                        display: flex;
                        gap: 8px;
                        align-items: center;
                    "
                >
                    <button
                        @click.prevent="startEdit(post)"
                        :disabled="isSubmitting && editingId === post.id"
                    >
                        Edit
                    </button>
                    <button
                        @click.prevent="removePost(post)"
                        :disabled="deletingId === post.id"
                        style="color: #c026d3"
                    >
                        <span v-if="deletingId === post.id">Deleting...</span>
                        <span v-else>Delete</span>
                    </button>
                    <small style="color: #9ca3af">Post #{{ post.id }}</small>
                </div>
            </li>
        </ul>
    </div>
</template>

<script setup>
import { ref } from "vue";
import axios from "axios";

const props = defineProps(["posts"]);
const posts = ref([...props.posts]);
const isSubmitting = ref(false);
const deletingId = ref(null);
const successMessage = ref("");

const newPost = ref({ title: "", content: "", category: "", tags: "" });
const editingId = ref(null);

function startEdit(post) {
    editingId.value = post.id;
    newPost.value = {
        title: post.title || "",
        content: post.content || "",
        category: post.category || "",
        tags: post.tags || "",
    };
}

async function createPost() {
    isSubmitting.value = true;
    try {
        if (editingId.value) {
            const response = await axios.put(
                `/posts/${editingId.value}`,
                newPost.value
            );
            if (response.data && response.data.post) {
                const idx = posts.value.findIndex(
                    (p) => p.id === response.data.post.id
                );
                if (idx !== -1) posts.value.splice(idx, 1, response.data.post);
            }
            successMessage.value = "Post updated successfully.";
            editingId.value = null;
        } else {
            const response = await axios.post("/posts", newPost.value);
            if (response.data && response.data.post) {
                posts.value.unshift(response.data.post);
            }
            newPost.value = { title: "", content: "", category: "", tags: "" };
            successMessage.value = "Post created successfully.";
        }
        setTimeout(() => (successMessage.value = ""), 3000);
    } catch (e) {
        console.error(e);
        alert("Error creating/updating post");
    } finally {
        isSubmitting.value = false;
    }
}

async function removePost(post) {
    if (!confirm("Are you sure?")) return;
    deletingId.value = post.id;
    try {
        const response = await axios.delete(`/posts/${post.id}`);
        if (response.status === 200) {
            const idx = posts.value.findIndex((p) => p.id === post.id);
            if (idx !== -1) posts.value.splice(idx, 1);
            successMessage.value = "Post deleted successfully.";
            setTimeout(() => (successMessage.value = ""), 3000);
        }
    } catch (e) {
        console.error(e);
        alert("Error deleting post");
    } finally {
        deletingId.value = null;
    }
}
</script>
