<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, router } from "@inertiajs/vue3";
import AddNewWebsiteModal from "./Dashboard_Parts/AddNewWebsiteModal.vue";
import axios from "axios";
import { onMounted } from "vue";
import { useCookies } from "@vueuse/integrations/useCookies";

defineProps({
    data: {
        required: true,
        type: Object,
    },
});

const cookies = useCookies();

const deleteWebsite = (websites_id) => {
    const url = route("website.destroy", {
        website_id: websites_id,
    });

    // console.log(url);

    router.delete(url);
};

onMounted(() => {
    const XSRF_TOKEN = cookies.get("XSRF-TOKEN") ?? null;
    // console.log(XSRF_TOKEN);

    if (typeof XSRF_TOKEN === "undefined" || XSRF_TOKEN == null) {
        axios
            .get("/sanctum/csrf-cookie")
            .then((res) => {})
            .catch((err) => {
                console.log("Error");
                console.log(err);
            });
    }
});

const isPrevious = (label) => {
    if ((label ?? "").toLowerCase().search("previous") !== -1) {
        return true;
    }

    return false;
};
const isNext = (label) => {
    if ((label ?? "").toLowerCase().search("next") !== -1) {
        return true;
    }

    return false;
};

const cleanLabel = (label) => {
    return (label ?? "").replace("&laquo;", "").replace("&raquo;", "");
};
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Dashboard
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="w-full my-6">
                    <div class="flex flex-row gap-3 float-right">
                        <AddNewWebsiteModal />
                        <!-- <AddNewWebsiteModal /> -->
                    </div>
                    <div class="clear-both"></div>
                </div>
                <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
                    <table
                        class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400"
                    >
                        <thead
                            class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400"
                        >
                            <tr>
                                <th scope="col" class="px-6 py-3">Websites</th>
                                <th scope="col" class="px-6 py-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="(elem, index) in data?.data ?? []"
                                class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600"
                            >
                                <th
                                    scope="row"
                                    class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white"
                                >
                                    {{ elem?.website ?? "" }}
                                </th>

                                <td class="px-6 py-4">
                                    <div class="inline-flex flex-row gap-x-3">
                                        <span
                                            @click="
                                                deleteWebsite(elem.id ?? '')
                                            "
                                            class="font-bold px-2 py-1 border shadow rounded uppercase text-xs text-red-600 hover:underline hover:cursor-pointer"
                                            >Delete</span
                                        >
                                        <a
                                            :href="
                                                elem?.website
                                                    ? `https://${elem.website}`
                                                    : '#'
                                            "
                                            target="_blank"
                                            class="font-bold px-2 py-1 border shadow rounded uppercase text-xs text-blue-600 hover:underline"
                                            >Visit</a
                                        >
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <nav
                        class="flex items-center flex-column flex-wrap md:flex-row justify-between p-4 gap-4"
                        aria-label="Table navigation"
                    >
                        <span
                            class="text-sm font-normal text-gray-500 dark:text-gray-400 mb-4 md:mb-0 block w-full md:inline md:w-auto"
                            >Total count:
                            <span
                                class="font-semibold text-gray-900 dark:text-white"
                            >
                                {{ data?.total }}</span
                            ></span
                        >
                        <template v-if="data?.links ?? false">
                            <ul
                                class="inline-flex -space-x-px rtl:space-x-reverse text-sm h-8"
                            >
                                <li v-for="(link, index) in data?.links ?? []">
                                    <a
                                        :href="link?.url ?? '#'"
                                        class="flex items-center justify-center px-3 h-8 ms-0 leading-tight text-gray-500 bg-white border border-gray-300 hover:bg-gray-100 hover:text-gray-700"
                                        :class="{
                                            'rounded-s-lg': isPrevious(
                                                link.label ?? ''
                                            ),
                                            'rounded-e-lg': isNext(
                                                link.label ?? ''
                                            ),
                                        }"
                                        >{{ cleanLabel(link.label ?? "") }}</a
                                    >
                                </li>
                            </ul>
                        </template>
                    </nav>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
