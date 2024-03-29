import React, {useEffect} from "react";
import {useLocation, useParams, withRouter} from "react-router-dom";
import {
	goPageTop,
} from "../../utils/Helpers";
import ParentCategories from "./includes/ParentCategories";
import CategoryProductList from "../product/productList/CategoryProductList";
import SidebarCategorySkeleton from "../../skeleton/productSkeleton/SidebarCategorySkeleton";
import {useAllCategories} from "../../api/GeneralApi";

function useQuery() {
	return new URLSearchParams(useLocation().search);
}

const LoadShopProducts = (props) => {

	let {category_slug} = useParams();
	const {data: categories, isLoading} = useAllCategories();

	const query = useQuery();
	const page = query.get("page");

	useEffect(() => {
		goPageTop();
	}, [category_slug, page]);


	const category = categories?.find(find => find.slug === category_slug) || {};

	return (
		<main className="main bg-gray">

			<div className="page-content">
				<div className="container">
					<div className="row">
						<aside className="col-lg-9">
							<div className="card my-5">
								<div className="card-body">
									<CategoryProductList
										slugKey={category_slug}
										category={category}
										currentPage={page ? page : 1}
									/>
								</div>
							</div>
						</aside>

						<aside className="col-lg-3 order-lg-first">
							{
								isLoading ?
									<SidebarCategorySkeleton/>
									:
									<ParentCategories slug={category_slug} category={category} categories={categories}/>
							}
						</aside>

					</div>
					{/* End .row */}
				</div>
				{/* End .container */}
			</div>
			{/* End .page-content */}
		</main>
	);
};


export default withRouter(LoadShopProducts);
