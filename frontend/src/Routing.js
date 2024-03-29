import React from "react";
import {Route, Switch} from "react-router-dom";
import Home from "./pages/home/Home";
import Login from "./auth/login/Login";
import My404Component from "./pages/404/My404Component";
import Faq from "./pages/faq/Faq";
import Contact from "./pages/contact/Contact";
import SinglePage from "./pages/single/SinglePage";
import ProductSingle from "./pages/product/productSingleNew/ProductSingle";
import Checkout from "./pages/checkout/Checkout";
import Wishlist from "./pages/wishlist/Wishlist";
import LoadCategory from "./pages/category/LoadCategory";
import LoadShopProducts from "./pages/shop/LoadShopProducts";
import LoadSearchProducts from "./pages/search/LoadSearchProducts";
import Dashboard from "./auth/dashboard/Dashboard";
import AuthRoute from "./AuthRoute";
import Payment from "./pages/payment/Payment";
import LoadPictureSearchProduct from "./pages/search/LoadPictureSearchProduct";
// import OrderDetails from "./auth/dashboard/orders/OrderDetails";
import AliExpressSearch from "./pages/aliexpress/search/AliExpressSearch";
import AliProductPage from "./pages/aliexpress/product/AliProductPage";
import ForgotPassword from "./auth/forgot/ForgotPassword";
import OnlinePaymentStatus from "./pages/payment/OnlinePaymentStatus";
import OrderDetails from "./auth/dashboard/orders/OrderDetails";
import AllOrders from "./auth/dashboard/orders/AllOrders";
import Profile from "./auth/dashboard/Profile";
import ManageAddress from "./auth/dashboard/address/ManageAddress";

const Routing = () => {
	return (
		<Switch>
			<Route path="/" exact component={Home}/>
			<Route path="/login" exact component={Login}/>
			<Route path="/forgot-password" exact component={ForgotPassword}/>
			<Route path="/faq" exact component={Faq}/>
			<Route path="/contact" exact component={Contact}/>

			<Route path="/pages/:slug" exact component={SinglePage}/>
			<Route path="/product/:item_id" exact component={ProductSingle}/>
			<Route
				path="/search"
				exact={true}
				component={LoadSearchProducts}
			/>
			<Route path="/search/picture/:search_id" exact component={LoadPictureSearchProduct}/>
			<Route
				path="/shop/:category_slug"
				exact
				component={LoadShopProducts}
			/>

			{/* start aliexpress route develop */}
			<Route path="/aliexpress/search" exact={true} component={AliExpressSearch}/>
			<Route path="/aliexpress/product/:productId" exact={true} component={AliProductPage}/>
			{/* end aliexpress route develop */}

			<AuthRoute path="/checkout" exact component={Checkout}/>
			<AuthRoute path="/payment" exact component={Payment}/>
			<AuthRoute path="/online/payment/:status" exact component={OnlinePaymentStatus}/>
			<AuthRoute path="/dashboard" exact component={Dashboard} />
			<AuthRoute path="/dashboard/orders" exact component={AllOrders} />
			<AuthRoute path="/dashboard/wishlist" exact component={Wishlist} />
			<AuthRoute path="/dashboard/address" exact component={ManageAddress} />
			<AuthRoute path="/dashboard/profile" exact component={Profile} />
			<AuthRoute path="/dashboard/orders/:tran_id" exact component={OrderDetails}/>


			<Route
				path="/:category_slug/:sub_slug?"
				exact
				component={LoadCategory}
			/>

			<Route path="*" exact component={My404Component}/>
		</Switch>
	);
};

export default Routing;
