<template>
  <div>
    <!-- Create Form -->
    <form @submit.prevent="createPost">
      <input v-model="newPost.title" placeholder="Title" required />
      <textarea v-model="newPost.body" placeholder="Body" required></textarea>
      <button type="submit">Add Post</button>
    </form>

    <!-- Display List -->
    <ul>
      <li v-for="post in posts" :key="post.id">
        <h3>{{ post.title }}</h3>
        <p>{{ post.body }}</p>
      </li>
    </ul>
  </div>
</template>

<script>
import { defineProps, ref } from 'vue';
import axios from 'axios';

export default {
  props: defineProps(['posts']),
  setup(props) {
    const newPost = ref({ title: '', body: '' });

    const createPost = async () => {
      await axios.post('/posts', newPost.value);
      location.reload(); // Reload to see new post
    };

    return { newPost, createPost };
  }
};
</script>