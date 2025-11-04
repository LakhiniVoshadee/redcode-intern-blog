<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Link, usePage } from "@inertiajs/vue3";

const page = usePage();
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Dashboard
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div
                    class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6"
                >
                    <div class="mb-4">
                        <p class="text-gray-700">
                            Welcome back,
                            <span class="font-medium text-gray-900">{{
                                page.props.auth?.user?.name || "User"
                            }}</span>
                            .
                        </p>
                    </div>

                    <div class="flex items-center gap-3">
                        <Link
                            :href="route('posts.index')"
                            class="inline-block px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700"
                        >
                            Go to Posts
                        </Link>

                        <!-- optional: quick-create shortcut (navigates to posts page and scrolls to create form) -->
                        <Link
                            :href="route('posts.index', { focus: 'create' })"
                            class="inline-block px-4 py-2 border border-purple-600 text-purple-600 rounded-lg hover:bg-purple-50"
                        >
                            Create Post
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
