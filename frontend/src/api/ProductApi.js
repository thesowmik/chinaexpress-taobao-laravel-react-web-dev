import {useMutation, useQuery} from "react-query";
import {instance} from "../utils/AxiosInstance";


export const randomString = () => {
   return [...Array(5)].map((value) => (Math.random() * 1000000).toString(36).replace('.', '')).join('');
};

export const isAuthenticated = () => {
   const isAuthenticated = window.localStorage.getItem('isAuthenticate');
   return isAuthenticated ? JSON.parse(isAuthenticated)?.login : false;
};

const recent_view_token = () => {
   const recent_view = window.localStorage.getItem('recent_view');
   if (recent_view) {
      return recent_view;
   }
   const token = randomString();
   localStorage.setItem('recent_view', token);
   return token;
};


export const useTabobaoProduct = (itemId) => useQuery(['product', itemId], async () => {
   const recent_view = recent_view_token();
   try {
      const {data} = await instance.post(`/product/${itemId}`, {recent_view});
      return data?.item ? data?.item : {};
   } catch (error) {
      console.log(error);
   }
});


export const useProductDetails = (product_id) => useQuery(["ProductDetails", product_id], async () => {
   try {
      const {data} = await instance.get(`/product-description/${product_id}`);
      return data?.description ? data?.description : {};
   } catch (error) {
      console.log(error);
   }
});


export const useRecentViewProducts = () => useQuery(["useRecentViewProducts"], async () => {
   const recent_view = recent_view_token();
   try {
      const {data} = await instance.post(`recent-view-products`, {recent_view});
      return data?.recentView ? JSON.parse(data?.recentView) : [];
   } catch (error) {
      console.log(error);
   }
});


export const useSectionProducts = (section) => useQuery(['section', section], async () => {
   try {
      const {data} = await instance.post(`get-section-products`, {section});
      return data?.products ? JSON.parse(data?.products)?.Content : [];
   } catch (error) {
      console.log(error);
   }
});


export const useRelatedProducts = (item_id) => useQuery(["relatedProducts"], async () => {
   try {
      const {data} = await instance.post(`related-products/${item_id}`);
      return data?.relatedProducts ? JSON.parse(data?.relatedProducts) : [];
   } catch (error) {
      console.log(error);
   }
});


export const useCategoryProducts = (slugKey, page = 1) => useQuery(["search", slugKey, page], async () => {
   try {
      const {data} = await instance.post(`category-products/${slugKey}`, {slugKey, page});
      return data?.products ? JSON.parse(data?.products) : {};
   } catch (error) {
      throw Error(error.response.statusText);
   }
});


export const useSearchKeyword = (keyword, page = 1) => useQuery(["search", keyword, page], async () => {
   try {
      const {data} = await instance.post(`search`, {keyword, page});
      return data?.products ? JSON.parse(data?.products) : {};
   } catch (error) {
      throw Error(error.response.statusText);
   }
});


export const useSearchPictureUpload = () => useMutation(["search-picture"], async (props) => {
   try {
      const {data} = await instance.post(`search-picture`, props);
      return data;
   } catch (error) {
      throw Error(error.response.statusText);
   }
});


export const usePictureSearch = (search_id, page = 1) => useQuery(["picture", search_id, page], async () => {
   try {
      const {data} = await instance.post(`get-picture-result/${search_id}`, {page});
      return data?.products ? JSON.parse(data?.products) : {};
   } catch (error) {
      throw Error(error.response.statusText);
   }
});





















