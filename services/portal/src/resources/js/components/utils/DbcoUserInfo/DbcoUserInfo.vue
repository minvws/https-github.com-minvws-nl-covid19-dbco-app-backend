<script lang="ts">
import type { PropType } from 'vue';
import { defineComponent } from 'vue';
import type { Organisation } from '@dbco/portal-api/organisation.dto';
import type { User } from '@dbco/portal-api/user';
import type { PermissionV1 } from '@dbco/enum';

export default defineComponent({
    name: 'DbcoUserInfo',
    props: {
        organisation: {
            type: Object as PropType<Organisation | []>, // can be an empty array when there is no user?
            required: true,
        },
        permissions: {
            type: Array as PropType<PermissionV1[] | null>,
            required: true,
        },
        user: {
            type: Object as PropType<User | null>,
            required: true,
        },
    },
    created() {
        if (this.user) {
            void this.$store.dispatch('userInfo/FILL', {
                organisation: this.organisation,
                permissions: this.permissions,
                user: this.user,
            });
        }
    },
    render() {
        return null;
    },
});
</script>
