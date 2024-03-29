import {useMutation, useQuery} from "react-query";
import {instance} from "../utils/AxiosInstance";


export const useHome = () => {

	const banner = useQuery(['banners'], async () => {
		try {
			const {data} = await instance.post(`banners`);
			return data?.banners ? JSON.parse(data?.banners) : [];
		} catch (error) {
			console.log(error);
		}
	});

	const lovingProducts = useQuery(['loving-products'], async () => {
		try {
			const {data} = await instance.post(`loving-products`);
			return data?.products ? JSON.parse(data?.products) : [];
		} catch (error) {
			console.log(error);
		}
	});

	return {
		banner,
		lovingProducts,
	}
};




export const useSettings = (process) => useQuery(["settings"], async (process) => {
	try {
		const {data} = await instance.post(`/general`, process);
		return data?.settings ? JSON.parse(data?.settings) : {};
	} catch (error) {
		console.log(error);
	}
});



export const usePageData = (slug) => useQuery(['pageData', slug], async () => {
	try {
		const {data} = await instance.get(`/page/${slug}`);
		return data?.singles ? data?.singles : {};
	} catch (error) {
		console.log(error);
	}
});



export const useCustomPageData = (url, Key) => useQuery(['customPageData', Key], async () => {
	try {
		const {data} = await instance.get(url);
		return data?.[Key] ? data?.[Key] : {};
	} catch (error) {
		console.log(error);
	}
});


export const useAllCategories = () => useQuery('allCategories', async () => {
	try {
		const startTime = new Date().getTime(); //one hour from now
		let saveData = localStorage.getItem('cats');
		saveData = saveData ? JSON.parse(saveData) : {};
		let expire = saveData?.expire || startTime;
		if (startTime < expire) {
			console.log('load cache');
			return saveData?.data;
		} else {
			const {data} = await instance.post(`/categories`, {});
			const expire = new Date(startTime + 30 * 60000).getTime(); // expire after 30 minutes
			const categories = data?.categories ? data?.categories : [];
			localStorage.setItem('cats', JSON.stringify({expire: expire, data: categories}));
			console.log('load server');
			return categories;
		}
	} catch (error) {
		throw Error(error.response.statusText);
	}
});


















