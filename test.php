<div class="d-flex align-items-center mb-2">
                                <i class="fas fa-clock me-2" style="color: var(--insurance-blue);"></i>
                                <span class="text-muted">
                                    <?= date("M j, Y g:i A", $eventDate) ?>
                                </span>
                            </div>

                            <div class="d-flex align-items-center mb-3">
                                <i class="fas fa-map-marker-alt me-2" style="color: var(--insurance-blue);"></i>
                                <span class="text-muted">
                                    <?= htmlspecialchars($event['location']) ?>
                                </span>
                            </div>

                            <p class="card-text text-muted">
                                <?= htmlspecialchars($event['description']) ?>
                            </p>