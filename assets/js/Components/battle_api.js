export function api_get(url) {
    console.log('request to', url);
    return axios.get(url);
}

export function api_post(url, data) {
    console.log('request to', url, data.data);
    return axios.post(url, {
        csrfToken: data.csrfToken,
        pokemonId: data.data
    });
}