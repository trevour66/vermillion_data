<script setup>
import { Head, Link, router } from "@inertiajs/vue3";
import { onMounted, reactive, ref } from "vue";
import axios from "axios";

defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
});

const slideContent = ref([
    { isActive: false, src: "/images/xlsx_format.PNG" },
    { isActive: false, src: "/images/csv_format.png" },
]);

const currentIndex = ref(0);
const file_processing_is_successful = ref(null);
const file_processing_is_not_successful = ref(null);
const file_processing_download_path = ref(null);

const form = reactive({
    file: null,
});

slideContent.value[0].isActive = true;

const nextSlide = () => {
    currentIndex.value = (currentIndex.value + 1) % slideContent.value.length;
};

setInterval(nextSlide, 5000); // Change slide every 3 seconds

const resetData = () => {
    file_processing_is_successful.value = null;
    file_processing_is_not_successful.value = null;
    file_processing_download_path.value = null;
};

const submit = () => {
    resetData();
    axios
        .postForm("/upload-file", {
            file: form.file,
        })
        .then(function (response) {
            console.log(response);

            file_processing_download_path.value = response.data.download_url;
        })
        .catch(function (error) {
            console.log(error);
        });
};
</script>

<template>
    <Head title="Welcome" />

    <div
        class="relative sm:flex sm:justify-center sm:items-center min-h-screen bg-dots-darker bg-center bg-gray-100 dark:bg-dots-lighter dark:bg-gray-900 selection:bg-red-500 selection:text-white"
    >
        <div
            v-if="canLogin"
            class="sm:fixed sm:top-0 sm:right-0 p-6 text-end bg-white/50 w-full flex items-start md:items-center justify-between gap-6 flex-col md:flex-row"
        >
            <div>
                <h2 class="text-xl font-bold leading-tight text-purple-900">
                    Vermillion Data
                </h2>
            </div>
            <div>
                <Link
                    v-if="$page.props.auth.user"
                    :href="route('dashboard')"
                    class="font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500"
                    >Dashboard</Link
                >

                <template v-else>
                    <Link
                        :href="route('login')"
                        class="font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500"
                        >Log in</Link
                    >

                    <Link
                        v-if="canRegister"
                        :href="route('register')"
                        class="ms-4 font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500"
                        >Register</Link
                    >
                </template>
            </div>
        </div>

        <div class="max-w-7xl mx-auto py-3 lg:py-4 px-3">
            <section class="lg:my-24 my-18">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-x-4">
                    <div
                        class="flex items-center justify-center px-4 py-10 sm:py-16 lg:py-24 bg-gray-50/50 sm:px-6 lg:px-8"
                    >
                        <div>
                            <div class="my-8 relative w-full h-full">
                                <img
                                    v-for="(data, index) in slideContent"
                                    :key="index"
                                    class="w-full md:w-[70%] max-h-full mx-auto transition ease-in-out delay-150"
                                    :class="{
                                        block: currentIndex == index,
                                        hidden: currentIndex !== index,
                                    }"
                                    :src="data.src"
                                    alt=""
                                />
                            </div>

                            <div class="w-full max-w-md mx-auto xl:max-w-xl">
                                <h3
                                    class="text-2xl font-bold text-center text-black"
                                >
                                    Duplicates checker
                                </h3>
                                <p
                                    class="leading-relaxed text-center text-gray-500 mt-2.5"
                                >
                                    The form enables you compare your list of
                                    websites with the websites available in our
                                    database. The aim is to identify duplicates.
                                </p>

                                <div
                                    class="flex items-center justify-center mt-10 space-x-3"
                                >
                                    <div
                                        v-for="(data, index) in slideContent"
                                        class="h-1.5 transition ease-in-out delay-150"
                                        :class="{
                                            'bg-purple-500 rounded-full w-20':
                                                currentIndex == index,
                                            'bg-gray-200 rounded-full w-12':
                                                currentIndex !== index,
                                        }"
                                    ></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div
                        class="flex items-center justify-center px-4 py-10 bg-white sm:px-6 lg:px-8 sm:py-16 lg:py-24 rounded-lg"
                    >
                        <div
                            class="xl:w-full xl:max-w-sm 2xl:max-w-md xl:mx-auto"
                        >
                            <h2
                                class="text-2xl font-bold leading-tight text-black sm:text-3xl"
                            >
                                Upload list of websites
                            </h2>
                            <p class="mt-2 text-base text-gray-600">
                                Currently we support csv and excel file format
                            </p>

                            <form @submit.prevent="submit" class="mt-8">
                                <div class="space-y-5">
                                    <div>
                                        <label
                                            class="text-base font-medium text-gray-900"
                                            for="data_file_upload"
                                            >Upload file</label
                                        >
                                        <div class="mt-2.5">
                                            <input
                                                class="block w-full p-4 text-black placeholder-gray-500 transition-all duration-200 border border-gray-200 rounded-md bg-gray-50 focus:outline-none focus:border-purple-600 focus:bg-white caret-purple-600"
                                                @input="
                                                    form.file =
                                                        $event.target.files[0]
                                                "
                                                type="file"
                                            />
                                        </div>
                                    </div>

                                    <div>
                                        <button
                                            type="submit"
                                            class="inline-flex items-center justify-center w-full px-4 py-2 text-base font-semibold text-white transition-all duration-200 bg-purple-600 border border-transparent rounded-md focus:outline-none hover:bg-purple-700 focus:bg-purple-700 uppercase"
                                        >
                                            Process data
                                        </button>
                                    </div>

                                    <div
                                        class="text-sm text-gray-500 dark:text-gray-300 my-3"
                                    >
                                        <template
                                            v-if="
                                                file_processing_is_not_successful
                                            "
                                        >
                                            <p
                                                class="font-semibold text-red-800"
                                            >
                                                {{
                                                    file_processing_is_not_successful
                                                }}
                                            </p>
                                        </template>
                                        <template
                                            v-else-if="
                                                file_processing_download_path
                                            "
                                        >
                                            <div class="my-3">
                                                <a
                                                    class="text-sm text-gray-500 uppercase underline"
                                                    :href="
                                                        file_processing_download_path
                                                    "
                                                    >Download processed file</a
                                                >
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </section>

            <div
                class="flex justify-center mt-16 px-6 sm:items-center sm:justify-between"
            >
                <div
                    class="text-center text-sm text-purple-500 sm:text-end sm:ms-0"
                >
                    Vermillion Data
                </div>
            </div>
        </div>
    </div>
</template>

<style>
.bg-dots-darker {
    background-image: url("data:image/svg+xml,%3Csvg width='30' height='30' viewBox='0 0 30 30' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M1.22676 0C1.91374 0 2.45351 0.539773 2.45351 1.22676C2.45351 1.91374 1.91374 2.45351 1.22676 2.45351C0.539773 2.45351 0 1.91374 0 1.22676C0 0.539773 0.539773 0 1.22676 0Z' fill='rgba(0,0,0,0.07)'/%3E%3C/svg%3E");
}
@media (prefers-color-scheme: dark) {
    .dark\:bg-dots-lighter {
        background-image: url("data:image/svg+xml,%3Csvg width='30' height='30' viewBox='0 0 30 30' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M1.22676 0C1.91374 0 2.45351 0.539773 2.45351 1.22676C2.45351 1.91374 1.91374 2.45351 1.22676 2.45351C0.539773 2.45351 0 1.91374 0 1.22676C0 0.539773 0.539773 0 1.22676 0Z' fill='rgba(255,255,255,0.07)'/%3E%3C/svg%3E");
    }
}
</style>
