<?php
class pager {
	private $_total;
    private $_availableLimit = array(10, 20, 50);
    private $_currentLimit;
    private $_pageNum;
	private $_currentPage;
	private $_pageBarNum = 3;
    private $_pageBarInitialized = false;
    private $_pageBarStart;
    private $_pageBarEnd;
	
	public function __construct($config)
	{
		$this->_total = (int) $config['total'];
		
		if (array_key_exists('pageBarNum', $config)) {
			$this->_pageBarNum = (int) $config['pageBarNum'];
		}
		
		if (array_key_exists('availableLimit', $config)) {
			$this->setAvailableLimit($config['availableLimit']);
		}
		
		if (array_key_exists('currentLimit', $config)) {
			$this->setCurrentLimit($config['currentLimit']);
		} else {
			$this->setCurrentLimit();
		}
		
		$this->_pageNum = ceil($this->_total/$this->_currentLimit);
		$this->setCurrentPage();
	}
	
	public function setAvailableLimit($limits)
    {
        if (is_array($limits) && count($limits) > 0) {
        	$this->_availableLimit = $limits;
        }
    }
    
	public function getAvailableLimit()
    {
        return $this->_availableLimit;
    }
    
    public function setCurrentLimit($limit=null)
    {
    	$this->_currentLimit = $this->_availableLimit[0];
    	
    	if ($limit!=null && in_array($limit, $this->_availableLimit)) {
    		$this->_currentLimit = $limit;
    	}
    	
    	if (isset($_GET['limit']) && in_array($_GET['limit'], $this->_availableLimit)) {
    		$this->_currentLimit = (int) $_GET['limit'];
    	}
    }
    
	public function setCurrentPage()
    {
    	$this->_currentPage = 1;
    	if (isset($_GET['page']) && $_GET['page'] <= $this->_pageNum && $_GET['page'] > 1) {
    		$this->_currentPage = $_GET['page'];
        }
    }
    public function getLimitSql()
    {
    	return $this->_pageNum > 1 ? ($this->_currentPage-1)*$this->_currentLimit.','.$this->_currentLimit:$this->_currentLimit;
    }
    
 	public function getPageNum()
    {
        return $this->_pageNum;
    }
    
    public function getFirstNum()
    {
        return $this->_currentLimit*($this->_currentPage-1)+1;
    }

    public function getLastNum()
    {
        return $this->_currentPage<$this->_pageNum? $this->_currentLimit*$this->_currentPage:$this->_total;
    }

    public function getTotalNum()
    {
        return $this->_total;
    }
    
	public function isFirstPage()
    {
        return $this->_currentPage == 1;
    }
    
	public function isLastPage()
    {
        return $this->_currentPage >= $this->getPageNum();
    }
    
    public function isLimitCurrent($limit)
    {
        return $limit == $this->_currentLimit;
    }

    public function isPageCurrent($page)
    {
        return $page == $this->_currentPage;
    }
    
    public function canShowFirst()
    {
    	return $this->getPageBarStart() > 1;
    }

    public function canShowLast()
    {
    	return $this->getPageBarEnd() < $this->_pageNum;
    }

    public function getPageBarStart()
    {
        $this->_initPageBar();
        return $this->_pageBarStart;
    }

    public function getPageBarEnd()
    {
        $this->_initPageBar();
        return $this->_pageBarEnd;
    }

    public function getPages()
    {
    	$start = $this->getPageBarStart();
        $end = $this->getPageBarEnd();
        return range($start, $end);
    }
    
    public function getFirstPageUrl()
    {
    	return $this->getPageUrl(1);
    }
    
	public function getPreviousPageUrl()
    {
        return $this->_currentPage>1 ? $this->getPageUrl($this->_currentPage-1):'';
    }

    public function getNextPageUrl()
    {
        return $this->getPageUrl($this->_currentPage+1);
    }
    
	public function getLastPageUrl()
    {
    	return $this->getPageUrl($this->_pageNum);
    }
    
	public function getLimitUrl($limit)
    {
    	return href_link($_GET['main_page'], get_all_get_params(array('page', 'limit')) . 'limit=' . $limit);
    }
    
    public function getPageUrl($page)
    {
    	return href_link($_GET['main_page'], get_all_get_params(array('page')) . 'page=' . $page);
    }
    
	protected function _initPageBar()
    {
        if (!$this->isPageBarInitialized()) {
            $start = 0;
            $end = 0;
            
            if ($this->_pageNum <= $this->_pageBarNum) {
                $start = 1;
                $end = $this->_pageNum;
            }
            else {
                $half = ceil($this->_pageBarNum / 2);
                if ($this->_currentPage >= $half && $this->_currentPage <= $this->_pageNum - $half) {
                    $start  = ($this->_currentPage - $half) + 1;
                    $end = ($start + $this->_pageBarNum) - 1;
                }
                elseif ($this->_currentPage < $half) {
                    $start  = 1;
                    $end = $this->_pageBarNum;
                }
                elseif ($this->_currentPage > ($this->_pageNum - $half)) {
                    $end = $this->_pageNum;
                    $start  = $end - $this->_pageBarNum + 1;
                }
            }
            $this->_pageBarStart = $start;
            $this->_pageBarEnd = $end;

            $this->_setPageBarInitialized(true);
        }

        return $this;
    }
    
    protected function _setPageBarInitialized($flag)
    {
        $this->_pageBarInitialized = (bool)$flag;
        return $this;
    }
    
    public function isPageBarInitialized()
    {
        return $this->_pageBarInitialized;
    }
    
    public function getCurrentPage()
    {
    	return $this->_currentPage;
    }
}
