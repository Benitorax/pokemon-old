export function api_get(url) {
    return axios.get(url);
}

export function api_post(url, data) {
    return axios.post(url, {
        csrfToken: data.csrfToken,
        pokemonUuid: data.data
    });
}