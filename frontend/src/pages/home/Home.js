import React from "react";
import Intro from "./includes/intro/Intro";
import ProductsLoving from "./includes/Products/productsLoving/ProductsLoving";
import AliExpressSearchBar from './includes/searchBar/AliExpressSearchBar';

import {useSettings} from "../../api/GeneralApi";
import SectionsProducts from "./sections/SectionsProducts";
import RecentProduct from "./includes/Products/recentProduct/RecentProduct";

const Home = (props) => {

   const {data: settings, isLoading} = useSettings();

   const section_one_active = settings?.section_one_active;
   const section_two_active = settings?.section_two_active;
   const section_three_active = settings?.section_three_active;
   const section_four_active = settings?.section_four_active;
   const section_five_active = settings?.section_five_active;

   const general = {};

   return (
      <main className="main" style={{backgroundColor: "#fafafa"}}>
         <Intro/>
         <AliExpressSearchBar/>
         {/* <IconBoxes/> */}
         {/*<PopularCategory/>*/}

         {section_one_active === "enable" && (
            <SectionsProducts settings={settings} section={'section_one'}/>
         )}
         {section_two_active === "enable" && (
            <SectionsProducts settings={settings} section={'section_two'}/>
         )}
         {section_three_active === "enable" && (
            <SectionsProducts settings={settings} section={'section_three'}/>
         )}
         {section_four_active === "enable" && (
            <SectionsProducts settings={settings} section={'section_four'}/>
         )}
         {section_five_active === "enable" && (
            <SectionsProducts settings={settings} section={'section_five'}/>
         )}

         <ProductsLoving/>

         <RecentProduct/>
         {/*<BrandProduct/>*/}
         {/*<Blog/>*/}
      </main>
   );
};


export default Home;
