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
            <button type="submit">Add Post</button>
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
                    <button @click.prevent="startEdit(post)">Edit</button>
                    <button
                        @click.prevent="removePost(post)"
                        style="color: #c026d3"
                    >
                        Delete
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
            editingId.value = null;
        } else {
            const response = await axios.post("/posts", newPost.value);
            if (response.data && response.data.post)
                posts.value.unshift(response.data.post);
            newPost.value = { title: "", content: "", category: "", tags: "" };
        }
    } catch (e) {
        console.error(e);
        alert("Error creating/updating post");
    }
}

async function removePost(post) {
    if (!confirm("Are you sure?")) return;
    try {
        const response = await axios.delete(`/posts/${post.id}`);
        if (response.status === 200) {
            const idx = posts.value.findIndex((p) => p.id === post.id);
            if (idx !== -1) posts.value.splice(idx, 1);
        }
    } catch (e) {
        console.error(e);
        alert("Error deleting post");
    }
}
</script>
