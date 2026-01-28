<style>
.table thead th {
    background-color: #f8f9fa;
}

.table-fixed {
    table-layout: fixed;
    width: 100%;
}

.table-fixed thead th,
.table-fixed tbody td {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.col-sl { width: 60px; }
.col-title { width: 25%; }
.col-description { width: 160px; text-align: center; }
.col-document { width: 140px; text-align: center; }
.col-actions { width: 120px; text-align: center; }
</style>

<section class="panel">
    <div class="panel-heading bg-primary text-white">
        <h4 class="panel-title mb-0"><i class="fas fa-book"></i> <?= translate('Library') ?></h4>
    </div>
    <div class="panel-body">
		<div class="row px-3 pt-3">
			<div class="col-md-offset-8 col-md-4 col-sm-12">
				<div class="form-group">
					<input type="text" id="bookSearch" class="form-control" placeholder="<?= translate('Search books...') ?>">
				</div>
			</div>
		</div>


        <?php foreach ($categorized_books as $category): ?>
            <div class="card shadow-sm mb-lg border-left-3 border-primary">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 text-dark"><i class="fas fa-folder-open text-primary"></i> <?= $category['category_name'] ?></h5>
                </div>

                <?php if (count($category['books']) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover table-condensed mb-none tbr-top">
                            <thead class="thead-light">
                                <tr>

                                    <th class="col-sl"><?= translate('SL') ?></th>
                                    <th class="col-title"><?= translate('Title') ?></th>
                                    <th class="col-description"><?= translate('Description') ?></th>
                                    <th class="col-document"><?= translate('Document') ?></th>
                                    <?php if (get_permission('policy', 'is_edit') || get_permission('policy', 'is_delete')): ?>
                                    <th class="col-actions"><?= translate('Actions') ?></th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $sl = 1; foreach ($category['books'] as $book): ?>
                                     <tr class="book-row">
                                        <td class="col-sl"><?= $sl++ ?></td>
                                        <td class="col-title"><strong><?= $book['title'] ?></strong></td>
                                        <td class="col-description">
                                          <a class="btn btn-info btn-circle icon" href="javascript:void(0);" data-toggle="tooltip" data-original-title="<?php echo translate('View Policy'); ?>"
                                                onclick="getViewOrder('<?php echo html_escape($book['id']); ?>')">
                                                    <i class="fas fa-eye" style="color: #ffffff;"></i> <?= translate('view') ?><!-- White color for better visibility on blue -->
                                                </a>
                                        </td>
                                        <td class="col-document">
                                            <?php if (!empty($book['document_enc_name'])): ?>
                                                <a href="<?= base_url('library/documents_download?file=' . urlencode($book['document_enc_name'])); ?>"
                                                   target="_blank"
                                                   class="btn btn-outline-primary btn-sm"
                                                   data-toggle="tooltip"
                                                   data-original-title="<?= translate('Download Document') ?>">
                                                    <i class="fas fa-file-download"></i> <?= translate('Download') ?>
                                                </a>
                                            <?php else: ?>
                                                <span class="badge badge-secondary"><?= translate('Not Available') ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <?php if (get_permission('policy', 'is_edit') || get_permission('policy', 'is_delete')): ?>
                                        <td class="col-actions">
                                            <?php if (get_permission('policy', 'is_edit')): ?>
                                                <a href="<?= base_url('library/book_edit/' . $book['id']); ?>"
                                                   class="btn btn-sm btn-outline-secondary"
                                                   data-toggle="tooltip"
                                                   title="<?= translate('Edit') ?>">
                                                    <i class="fas fa-pen-nib"></i>
                                                </a>
                                            <?php endif; ?>

                                            <?php if (get_permission('policy', 'is_delete')): ?>
                                                <?= btn_delete('library/book_delete/' . $book['id']); ?>
                                            <?php endif; ?>
                                        </td>
                                        <?php endif; ?>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="card-body">
                        <p class="text-muted mb-0"><i class="fas fa-info-circle"></i> <?= translate('No documents found in this category.') ?></p>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<div class="zoom-anim-dialog modal-block modal-block-lg mfp-hide payroll-t-modal" id="modal_equipment_details" style="width: 100%!important;">
    <section class="panel">
        <header class="panel-heading d-flex justify-content-between align-items-center">
            <div class="row">
                <div class="col-md-6 text-left">
                    <h4 class="panel-title">
						<i class="fas fa-bars"></i> <?php echo translate('Policy') . " " . translate('Description'); ?>
					</h4>
                </div>
                <div class="col-md-5 text-right">
                    <!-- Print Button in Footer -->
                    <button class="btn btn-primary" onclick="printDescription()">🖨️ Print</button>
                </div>
            </div>
        </header>
        <div class="panel-body">
            <div id="equipment_details_view_tray">
                <!-- The description content will be loaded here dynamically -->
            </div>
        </div>
        <footer class="panel-footer">
            <div class="row">
                <div class="col-md-6 text-left">
                    <!-- Print Button in Footer -->
                    <button class="btn btn-primary" onclick="printDescription()">🖨️ Print</button>
                </div>
                <div class="col-md-6 text-right">
                    <button class="btn btn-default modal-dismiss"><?php echo translate('close'); ?></button>
                </div>
            </div>
        </footer>
    </section>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('bookSearch');
        searchInput.addEventListener('keyup', function () {
            const filter = searchInput.value.toLowerCase();
            const rows = document.querySelectorAll('.book-row');
            
            rows.forEach(row => {
                const title = row.querySelector('.col-title').textContent.toLowerCase();
                const description = row.querySelector('.col-description').textContent.toLowerCase();
                
                if (title.includes(filter) || description.includes(filter)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    });
</script>


<script>
function getViewOrder(id) {
    $.ajax({
        url: base_url + 'library/get_view_description', // Update the URL to the correct controller path
        type: 'POST',
        data: { id: id },
        success: function(response) {
            // Inject the response into the modal
            $('#equipment_details_view_tray').html(response);

            // Open the modal
            $.magnificPopup.open({
                items: {
                    src: '#modal_equipment_details'
                },
                type: 'inline'
            });
        },
        error: function() {
            alert('Failed to retrieve description.');
        }
    });
}

function printDescription() {
    const content = document.getElementById('equipment_details_view_tray').innerHTML;
    
    // Get the title from the currently displayed policy in the modal
    let bookTitle = "Policy";
    const titleElement = document.querySelector('#equipment_details_view_tray h3, #equipment_details_view_tray h2, #equipment_details_view_tray .policy-title');
    
    if (titleElement) {
        bookTitle = titleElement.textContent.trim();
    } else {
        // Try to find any title in the content
        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = content;
        const firstHeading = tempDiv.querySelector('h1, h2, h3, h4, strong');
        if (firstHeading) {
            bookTitle = firstHeading.textContent.trim();
        }
    }
    
    // Clean the title for use as filename
    const safeBookTitle = bookTitle.replace(/[^A-Za-z0-9]/g, '_');

    const html = `
    <html>
    <head>
        <title>${safeBookTitle}</title>
        <style>
            @media print {
                @page {
                    size: A4;
                    margin: 20mm 25mm;
                }
                html, body {
                    margin: 0;
                    padding: 0;
                    width: 100%;
                    height: 100%;
                    -webkit-print-color-adjust: exact !important;
                    print-color-adjust: exact !important;
                }
            }

            body {
                font-family: Arial, sans-serif;
                background: white;
            }

            .content-wrapper {
                margin: 0 auto;
                padding: 0; /* No extra padding since margin is set via @page */
                box-sizing: border-box;
            }

            h1, h2 {
                text-align: center;
                margin-bottom: 10px;
            }

            h1 {
                font-size: 20px;
            }

            h2 {
                font-size: 26px;
                font-weight: bold;
                margin-bottom: 5px;
            }

            hr {
                margin: 10px 0 20px;
            }

            .content {
                overflow: hidden;
            }

            .page-break {
                page-break-before: always;
            }
        </style>
    </head>
    <body>
        <div class="content-wrapper">
            <h1>${bookTitle}</h1>
            <hr>
            <div class="content">${content}</div>
        </div>
    </body>
    </html>
    `;

    const printWindow = window.open('', '', 'width=800,height=600');
    printWindow.document.open();
    printWindow.document.write(html);
    printWindow.document.close();

    printWindow.focus();
    setTimeout(() => {
        printWindow.document.title = safeBookTitle + "_Policy_Description";
        printWindow.print();
        printWindow.close();
    }, 500);
}

</script>
