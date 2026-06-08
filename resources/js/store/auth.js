import axios from 'axios';
import { ref } from "vue";
import { defineStore } from "pinia";

export const authStore = defineStore("authStore", () => {

    let user = ref({name:''});
    let authenticated = ref(false);

    // USO DE FUNCIONES JAVASCRIPT AVANZADAS: async/await y try/catch
    async function login(data) {
        try {
            const response = await axios.get('/api/user');
            user.value = response.data?.data;
            authenticated.value = true;
        } catch (error) {
            user.value = {};
            authenticated.value = false;
        }
    }

    async function getUser(data) {
        try {
            const response = await axios.get('/api/user');
            user.value = response.data?.data;
            authenticated.value = true;
            console.log('getUser AT: true ');
            console.log(user.value);
        } catch (error) {
            console.log('getUser: error ');
            user.value = {};
            authenticated.value = false;
        }
    }

    async function getUserSignIn(data) {
        try {
            const response = await axios.get('/api/user/signin');
            user.value = response.data?.data;
            authenticated.value = true;
        } catch (error) {
            console.log('getUserSignIn: error ');
            user.value = {};
            authenticated.value = false;
        }
    }

    function logout() {
        user.value = {};
        authenticated.value = false;
    }

    function is(roleName) {
        // USO DE FUNCIONES JAVASCRIPT AVANZADAS: encadenamiento opcional para evitar fallos si roles no existe
        return user.value?.roles?.some(role => role.name === roleName) ?? false;
    }

    return { user, authenticated, login, is, getUser, getUserSignIn, logout};
}, {persist: true});
