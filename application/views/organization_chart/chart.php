<style>
.tree {
    display: flex;
    justify-content: center;
    align-items: center;
    flex-wrap: wrap;
    margin: 30px 0;
    overflow-x: auto;
}

.tree ul {
    padding: 15px 0;
    position: relative;
    transition: all 0.5s;
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
}

.tree li {
    display: flex;
    flex-direction: column;
    align-items: center;
    list-style-type: none;
    position: relative;
    padding: 15px 5px 0 5px;
    transition: all 0.5s;
}

.tree li::before,
.tree li::after {
    content: '';
    position: absolute;
    top: 0;
    right: 50%;
    border-top: 2px solid #ccc;
    width: 50%;
    height: 15px;
}

.tree li::after {
    right: auto;
    left: 50%;
    border-left: 2px solid #ccc;
}

.tree li:first-child::before {
    border: none;
}

.tree li:last-child::after {
    border: none;
}

.tree li:only-child::before,
.tree li:only-child::after {
    display: none;
}

.tree li:only-child {
    padding-top: 0;
}

.tree ul ul::before {
    content: '';
    position: absolute;
    top: 0;
    left: 50%;
    border-left: 2px solid #ccc;
    width: 0;
    height: 15px;
}

.tree li .box {
    padding: 2px 2px;
    border: 1px solid #ccc;
    background: #fff;
    border-radius: 6px;
    transition: all 0.5s;
    cursor: pointer;
    max-width: 160px;
    min-width: 130px;
    text-align: left;
    box-shadow: 0 2px 4px rgba(0,0,0,0.08);
}

.tree li .box:hover {
    background: #f5f5f5;
    transform: translateY(-1px);
    box-shadow: 0 3px 6px rgba(0,0,0,0.12);
}

.tree li .box strong {
    font-size: 10px;
}

.designation {
    font-size: 9px;
    color: #666;
    margin-top: 0px;
    display: block;
}

@media (max-width: 768px) {
    .tree {
        margin: 15px 0;
        padding: 0 15px;
        background: #fafafa;
        border-radius: 8px;
    }
    
    .tree ul {
        flex-direction: column;
        align-items: flex-start;
        padding: 0;
        margin-left: 25px;
        border-left: 2px solid #007bff;
        padding-left: 20px;
    }
    
    .tree > ul {
        margin-left: 0;
        border-left: none;
        padding-left: 0;
    }
    
    .tree li {
        padding: 8px 0;
        margin: 5px 0;
        align-items: flex-start;
        position: relative;
    }
    
    .tree li::before {
        content: '';
        position: absolute;
        left: -20px;
        top: 30px;
        width: 18px;
        height: 2px;
        background: #007bff;
        border-radius: 1px;
    }
    
    .tree li::after {
        display: none;
    }
    
    .tree ul ul::before {
        display: none;
    }
    
    .tree li .box {
        max-width: 100%;
        min-width: 220px;
        padding: 12px;
        margin-bottom: 8px;
        background: linear-gradient(135deg, #fff 0%, #f8f9fa 100%);
        border: 1px solid #e3e6ea;
        box-shadow: 0 2px 8px rgba(0,123,255,0.1);
    }
    
    .tree li .box:hover {
        transform: translateX(3px);
        box-shadow: 0 4px 12px rgba(0,123,255,0.15);
    }
    
    .tree li .box strong {
        font-size: 13px;
        color: #2c3e50;
        font-weight: 600;
    }
    
    .designation {
        font-size: 11px;
        color: #6c757d;
        font-weight: 500;
    }
}
</style>


<section class="panel">
<?php if (!empty($org_chart) && !empty($org_chart[0]) && !empty($org_chart[0]['name'])): ?>
<div class="tree">
    <?php
   function render_vertical_tree($nodes) {
	if (empty($nodes)) return;

	echo '<ul>';
	foreach ($nodes as $node) {
		echo '<li>';
		echo '<div class="box">';
		echo '<div style="display:flex;align-items:center;gap:5px;">';

		// Staff photo
		if (!empty($node['photo'])) {
			echo '<img src="' . get_image_url('staff', $node['photo']) . '" height="50" style="width:40px;height:40px;border-radius:50%;object-fit:cover;">';
		} else {
			echo '<img src="' . get_image_url('staff', '') . '" height="50" style="width:40px;height:40px;border-radius:50%;object-fit:cover;">';
		}

		// Name & Designation
		echo '<div>';
		echo '<strong>' . html_escape($node['name']) . '</strong>';
		if (!empty($node['designation'])) {
			echo '<br><span class="designation">' . html_escape($node['designation']) . '</span>';
		}
		echo '</div>';

		echo '</div>'; // end flex container
		echo '</div>'; // end box

		// Recurse
		if (!empty($node['children'])) {
			render_vertical_tree($node['children']);
		}

		echo '</li>';
	}
	echo '</ul>';
}


    render_vertical_tree($org_chart);
    ?>
</div>
<?php else: ?>
<div style="text-align:center;padding:50px;color:#666;">No data found</div>
<?php endif; ?>
</section>
