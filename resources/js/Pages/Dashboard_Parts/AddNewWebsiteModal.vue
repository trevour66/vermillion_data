<script setup>
import { initFlowbite, initModals } from "flowbite";
import { computed, onMounted, reactive, ref } from "vue";
import axios from "axios";
import { router } from "@inertiajs/vue3";

const websites_input = ref("");

const submittingData = ref(false);
const error_submittingData = ref(false);
const success_submittingData = ref(false);

const improperly_formatted_domains_result = ref([]);

const modal_closer = ref(null);

const improperly_formatted_domains_string = computed(() => {
    let data = improperly_formatted_domains_result.value.join(", ") ?? "";

    return data;
});

const reAuth = async () => {
    await axios
        .get("/sanctum/csrf-cookie")
        .then((res) => {})
        .catch((err) => {
            console.log("Error reauth");
            console.log(err);
        });
};

const submitWebsite = async () => {
    if (submittingData.value == true || websites_input.value == "") {
        return;
    }

    submittingData.value = true;
    error_submittingData.value = false;
    success_submittingData.value = false;

    const splitedWebsites = websites_input.value?.split(",") ?? null;
    console.log(splitedWebsites);

    axios
        .post(route("dashboard.add_websites_api"), {
            websites: splitedWebsites,
        })
        .then((res) => {
            console.log(res);

            const data = res.data ?? null;
            const status = res?.data?.status ?? null;

            improperly_formatted_domains_result.value =
                res?.data?.improperly_formatted_domains ?? [];

            if (status == "success") {
                success_submittingData.value = true;
            } else {
                error_submittingData.value = true;
            }

            submittingData.value = false;
            websites_input.value = "";
            router.reload();
            // modal_closer.value.click();
        })
        .catch(async (err) => {
            console.log("Error");
            console.log(err);

            if (
                (err.status == 419 || err.status == 401) &&
                (err.response.data?.message ?? "").indexOf(
                    "CSRF token mismatch"
                ) >= 0
            ) {
                await reAuth();
                return;
            }

            error_submittingData.value = true;
            submittingData.value = false;
        });
};

onMounted(() => {
    initFlowbite();
    initModals();
});
</script>
<template>
    <!-- Modal toggle -->

    <button
        data-modal-target="add_websites_modal"
        data-modal-toggle="add_websites_modal"
        type="button"
        class="float-right text-gray-900 bg-white border border-gray-300 focus:outline-none hover:bg-gray-100 focus:ring-4 focus:ring-gray-100 font-semibold rounded-lg text-xs px-3 py-1.5 me-2 uppercase"
    >
        Add new website(s)
    </button>

    <!-- Main modal -->
    <div
        id="add_websites_modal"
        data-modal-backdrop="static"
        tabindex="-1"
        aria-hidden="true"
        class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full"
    >
        <div class="relative p-4 w-full max-w-2xl max-h-full">
            <!-- Modal content -->
            <div class="relative bg-white rounded-lg shadow dark:bg-gray-700">
                <!-- Modal header -->
                <div
                    class="flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600"
                >
                    <h3
                        class="text-xl font-semibold text-gray-900 dark:text-white"
                    >
                        Add Websites
                    </h3>
                    <button
                        ref="modal_closer"
                        type="button"
                        class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white"
                        data-modal-hide="add_websites_modal"
                    >
                        <svg
                            class="w-3 h-3"
                            aria-hidden="true"
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 14 14"
                        >
                            <path
                                stroke="currentColor"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"
                            />
                        </svg>
                        <span class="sr-only">Close modal</span>
                    </button>
                </div>
                <!-- Modal body -->
                <div class="p-4 md:p-5 space-y-4">
                    <div
                        v-if="improperly_formatted_domains_string !== ''"
                        class="py-2 max-w-sm mx-auto text-xs"
                    >
                        <h5>
                            The following website were rejected because they are
                            not properly formatted:
                        </h5>
                        <p class="font-semibold mt-2">
                            {{ improperly_formatted_domains_string }}
                        </p>
                    </div>
                    <form class="max-w-sm mx-auto my-4">
                        <textarea
                            v-model="websites_input"
                            rows="4"
                            class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                            placeholder="Add websites"
                        ></textarea>
                        <p
                            id="helper-text-explanation"
                            class="mt-2 text-xs text-gray-500 dark:text-gray-400"
                        >
                            Please use the format www.abc.xyx(i.e. www.[the
                            domain name]) for the websites. Also, multiple
                            websites can be seperated buy a comma(,).
                        </p>
                    </form>
                </div>
                <!-- Modal footer -->
                <div
                    class="flex items-center p-4 md:p-5 border-t border-gray-200 rounded-b dark:border-gray-600"
                >
                    <div class="w-full">
                        <button
                            :disabled="submittingData || websites_input == ''"
                            @click="submitWebsite"
                            type="button"
                            class="float-right text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-xs px-4 py-2 text-center uppercase"
                            :class="{
                                'hover:cursor-not-allowed':
                                    submittingData || websites_input == '',
                            }"
                        >
                            <svg
                                v-if="submittingData"
                                aria-hidden="true"
                                role="status"
                                class="inline w-4 h-4 me-3 text-gray-200 animate-spin dark:text-gray-600"
                                viewBox="0 0 100 101"
                                fill="none"
                                xmlns="http://www.w3.org/2000/svg"
                            >
                                <path
                                    d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z"
                                    fill="currentColor"
                                />
                                <path
                                    d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z"
                                    fill="#1C64F2"
                                />
                            </svg>
                            Save Websites
                        </button>
                        <div class="clear-both"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
