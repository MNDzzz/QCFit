<template>
    <div class="outfits-page">
        <Card>
            <template #title>
                <div class="flex items-center justify-between w-full">
                    <span>Outfit Moderation</span>
                    <div class="flex items-center gap-2">
                        <Button
                            label="Refresh"
                            icon="pi pi-refresh"
                            size="small"
                            outlined
                            severity="secondary"
                            :loading="isLoading"
                            @click="getOutfits"
                        />
                    </div>
                </div>
            </template>

            <template #subtitle>
                Review and moderate user-created outfits. Remove inappropriate content or items that violate guidelines.
            </template>

            <template #content>
                <div v-if="isLoading" class="table-loading-skeleton space-y-3">
                    <div
                        v-for="row in skeletonRows"
                        :key="row"
                        class="flex gap-3 items-center"
                    >
                        <Skeleton width="60px" height="1.25rem" />
                        <Skeleton width="50px" height="50px" />
                        <Skeleton width="220px" height="1.25rem" />
                        <Skeleton width="160px" height="1.25rem" />
                        <Skeleton width="100px" height="1.25rem" />
                        <div class="flex gap-2 ml-auto">
                            <Skeleton width="2.5rem" height="2.5rem" shape="circle" />
                        </div>
                    </div>
                </div>
                <DataTable
                    v-else
                    v-model:filters="filters"
                    :value="outfits || []"
                    :paginator="true"
                    :rows="10"
                    :rows-per-page-options="[10, 25, 50]"
                    data-key="id"
                    striped-rows
                    size="small"
                    :loading="isLoading"
                    filter-display="menu"
                    :filter-delay="300"
                    :global-filter-fields="['id', 'title']"
                >
                    <template #empty>
                        <div class="table-empty-state">
                            <i class="pi pi-inbox empty-state-icon"></i>
                            <p class="empty-state-text">No outfits found</p>
                            <p class="empty-state-subtext">No outfits have been created in the system</p>
                        </div>
                    </template>

                    <Column field="id" header="ID" sortable class="w-[80px]">
                        <template #body="slotProps">
                            <span class="table-cell-id">#{{ slotProps.data.id }}</span>
                        </template>
                    </Column>

                    <Column header="Thumbnail" class="w-[80px]">
                        <template #body="slotProps">
                            <img
                                v-if="slotProps.data.thumbnail_url"
                                :src="slotProps.data.thumbnail_url"
                                referrerpolicy="no-referrer"
                                class="w-12 h-12 object-cover rounded-lg border border-gray-200"
                                alt="Thumbnail"
                            />
                            <div v-else class="w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center">
                                <i class="pi pi-image text-gray-400"></i>
                            </div>
                        </template>
                    </Column>

                    <Column field="title" header="Title" sortable filter class="min-w-[200px]">
                        <template #body="slotProps">
                            <span class="table-cell-name font-medium">{{ slotProps.data.title || 'Untitled' }}</span>
                        </template>
                        <template #filter="{ filterModel }">
                            <InputText v-model="filterModel.value" type="text" placeholder="Search by title" />
                        </template>
                    </Column>

                    <Column header="Author" class="min-w-[160px]">
                        <template #body="slotProps">
                            <div v-if="slotProps.data.user" class="flex items-center gap-2">
                                <Avatar
                                    :image="slotProps.data.user.avatar"
                                    :label="slotProps.data.user.name ? slotProps.data.user.name[0] : '?'"
                                    shape="circle"
                                    size="small"
                                />
                                <span class="text-sm">{{ slotProps.data.user.name }}</span>
                            </div>
                            <span v-else class="text-gray-400 text-sm">Unknown</span>
                        </template>
                    </Column>

                    <Column header="Items" class="w-[80px]">
                        <template #body="slotProps">
                            <Tag
                                :value="(slotProps.data.products_count || 0) + ' items'"
                                severity="info"
                            />
                        </template>
                    </Column>

                    <Column field="created_at" header="Date" sortable class="min-w-[150px]">
                        <template #body="slotProps">
                            <span class="text-sm table-cell-date">
                                <i class="pi pi-calendar mr-2 text-xs opacity-70"></i>
                                {{ formatDate(slotProps.data.created_at) }}
                            </span>
                        </template>
                    </Column>

                    <Column header="Actions" class="w-[100px]">
                        <template #body="slotProps">
                            <div class="flex gap-2">
                                <Button
                                    v-if="can('outfit-delete')"
                                    v-tooltip.top="'Delete outfit'"
                                    icon="pi pi-trash"
                                    rounded
                                    text
                                    severity="danger"
                                    size="small"
                                    @click="confirmDeleteOutfit(slotProps.data)"
                                />
                            </div>
                        </template>
                    </Column>
                </DataTable>
            </template>
        </Card>
    </div>
</template>

<script setup>
import { ref, onMounted, inject } from "vue";
import useOutfits from "@/composables/outfits";
import { useAbility } from '@casl/vue';
import { FilterMatchMode, FilterOperator } from "@primevue/core/api";

const { outfits, getOutfits, deleteOutfit, isLoading } = useOutfits();
const { can } = useAbility();
const swal = inject('$swal');

const skeletonRows = Array.from({ length: 5 }, (_, index) => index);

const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
    title: { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.STARTS_WITH }] },
});

const confirmDeleteOutfit = (outfit) => {
    if (!swal) {
        deleteOutfit(outfit.id);
        return;
    }

    swal({
        icon: 'warning',
        title: 'Delete outfit?',
        text: `The outfit "${outfit.title || 'Untitled'}" by ${outfit.user?.name || 'unknown user'} will be permanently deleted.`,
        showCancelButton: true,
        confirmButtonText: 'Yes, delete',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#ef4444'
    }).then((result) => {
        if (result.isConfirmed) {
            deleteOutfit(outfit.id);
        }
    });
};

const formatDate = (dateString) => {
    if (!dateString) return '-';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
};

onMounted(() => {
    getOutfits();
});
</script>
