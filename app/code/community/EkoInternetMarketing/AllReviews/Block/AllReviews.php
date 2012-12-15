<?php
class EkoInternetMarketing_AllReviews_Block_AllReviews extends Mage_Core_Block_Template
{

	public function getReviews($numberOfReviews){
				
		$_reviews = Mage::getModel('review/review')
			->getResourceCollection()
			->addStoreFilter(Mage::app()->getStore()->getId()) 
			->addStatusFilter(Mage_Review_Model_Review::STATUS_APPROVED)
			->setDateOrder('desc')
			->setPageSize($numberOfReviews)
			->addRateVotes();
			
		return $_reviews;
	}
	
	public function getProductDetails(){
		
		
		
	}
	
	public function getAllReviews(){
		
		// Get the number of reviews to display
		$numberOfReviews = Mage::getStoreConfig('ekoim/allreviews/number_of_reviews');

		// Get the reviews
		$_reviews = $this->getReviews($numberOfReviews);
		
		// Get the settings
		$enable_product_image = Mage::getStoreConfig('ekoim/allreviews/enable_product_image');
		$enable_totals = Mage::getStoreConfig('ekoim/allreviews/enable_totals');

		$data['settings']['number_of_columns'] = Mage::getStoreConfig('ekoim/allreviews/number_of_columns');
		$data['settings']['enable_review_nickname'] = Mage::getStoreConfig('ekoim/allreviews/enable_review_nickname');
		$data['settings']['enable_review_title'] = Mage::getStoreConfig('ekoim/allreviews/enable_review_title');
		$data['settings']['enable_review_date'] = Mage::getStoreConfig('ekoim/allreviews/enable_review_date');
		$data['settings']['enable_review_stars'] = Mage::getStoreConfig('ekoim/allreviews/enable_review_stars');
		$data['settings']['enable_totals'] = Mage::getStoreConfig('ekoim/allreviews/enable_totals');
		$data['settings']['enable_rich_snippet'] = Mage::getStoreConfig('ekoim/allreviews/enable_rich_snippet');
		$data['settings']['enable_module_credit'] = Mage::getStoreConfig('ekoim/allreviews/enable_module_credit');
		
		if($enable_totals == "1"):
			$data['totals'] = $this->getTotalReviews();
		endif;
		
		$i=0;
		foreach($_reviews as $review):
			
			$data['reviews'][$i]['review_title'] = $review->getTitle();
			$data['reviews'][$i]['review_nickname'] = $review->getNickname();
			$data['reviews'][$i]['review_created_at'] = $this->formatCreatedDate($review->getCreatedAt(), "m/d/Y");
			$data['reviews'][$i]['review_detail'] = $review->getDetail();
			$data['reviews'][$i]['review_percentage'] = $this->getReviewFinalPercentage($review->getRatingVotes());
			
			if($enable_product_image == "1"):
				
				$_product = Mage::getModel('catalog/product')->load($review->getData('entity_pk_value'));
				
				$data['reviews'][$i]['product_image'] = '<a href="'.$_product->getProductUrl().'" title="'.$this->stripTags($this->getImageLabel($_product, 'small_image'), null, true).'" class="product-image"><img src="'.$this->helper('catalog/image')->init($_product, 'small_image')->resize(80).'" width="80" height="80" alt="'.$this->stripTags($this->getImageLabel($_product, 'small_image'), null, true).'" /></a>';
				
			endif;
			
			
			$i++;
		endforeach;
		
		return $data;
	}
	
	public function formatCreatedDate($date, $format){
		
		$date = strtotime($date);
		$reviewDate = date($format, $date);
		
		return $reviewDate;
	}
	
	public function getReviewFinalPercentage($votes){

		$cumulativeRating = 0;
		$j=0;
		foreach( $votes as $vote ) {
			$cumulativeRating +=$vote->getPercent();
			$j++;
		}
	
		$finalPercentage = 0;
		if ($cumulativeRating != 0){
			$finalPercentage = ($cumulativeRating/$j);
		}
		
		return $finalPercentage;
	}
	
	public function getModuleCredit(){
		
		$showModuleCreditValue = Mage::getStoreConfig('ekoim/allreviews/show_module_credit');
		
		$credit = 'Magento Module By <a href="http://www.ekoim.com">EKO Internet Marketing</a>';
		
		if($showModuleCreditValue == "0"):
			$credit = '<a href="http://www.ekoim.com" style="display: none;">EKO Internet Marketing</a>';
		endif;
		
		return $credit;		
	}
	
	public function getTotalReviews(){
		
		$_reviews = $this->getReviews();
		
		$i=0;
		$cumulativePercentage = 0;
		foreach($_reviews as $review):
			
			$cumulativePercentage = $cumulativePercentage + $this->getReviewFinalPercentage($review->getRatingVotes());
		
			$i++;	
		endforeach;
		
		$totalReviewRating['total_reviews'] = $i;
		$totalReviewRating['total_percentage'] = ceil($cumulativePercentage / $i);
		$totalReviewRating['total_of_five'] = round($totalReviewRating['total_percentage'] / 100 * 5, 2);
				
		return $totalReviewRating;
	}

}
?>