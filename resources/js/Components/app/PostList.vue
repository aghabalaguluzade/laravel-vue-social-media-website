<script setup>
import PostItem from "@/Components/app/PostItem.vue";
import PostModal from "@/Components/app/PostModal.vue";
import { ref } from "vue";

defineProps({
    posts : Array
})

const showEditModal = ref(false)
const editPost = ref({})

function openEditModal(post) {
    editPost.value = post;
    showEditModal.value = true;
}

</script>

<template>
    <div class="overflow-auto">
        <PostItem v-for="post of posts" :key="post.id" :post="post" @editClick="openEditModal"/>
        <PostModal :post="editPost" v-model="showEditModal"/>
    </div>
</template>

<style scoped>
</style>