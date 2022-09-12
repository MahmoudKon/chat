<!-- Sidebar -->
<aside class="sidebar bg-light">
    <div class="tab-content h-100" role="tablist">
        <!-- Create -->
        <div class="tab-pane fade h-100" id="tab-content-create-chat" role="tabpanel"></div>

        <!-- Friends -->
        <div class="tab-pane fade h-100" id="tab-content-friends" role="tabpanel">
            <div class="d-flex flex-column h-100">
                <div class="hide-scrollbar">
                    <div class="container py-8">

                        <!-- Title -->
                        <div class="mb-8">
                            <h2 class="fw-bold m-0">Friends</h2>
                        </div>

                        <!-- Search -->
                        <div class="mb-6">
                            <form action="#">
                                <div class="input-group">
                                    <div class="input-group-text">
                                        <div class="icon icon-lg">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                                stroke-linecap="round" stroke-linejoin="round"
                                                class="feather feather-search">
                                                <circle cx="11" cy="11" r="8"></circle>
                                                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                                            </svg>
                                        </div>
                                    </div>

                                    <input type="text" class="form-control form-control-lg ps-0"
                                        placeholder="Search messages or users"
                                        aria-label="Search for messages or users...">
                                </div>
                            </form>

                            <!-- Invite button -->
                            <div class="mt-5">
                                <a href="#" class="btn btn-lg btn-primary w-100 d-flex align-items-center"
                                    data-bs-toggle="modal" data-bs-target="#modal-invite">
                                    Find Friends

                                    <span class="icon ms-auto">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round"
                                            class="feather feather-user-plus">
                                            <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                            <circle cx="8.5" cy="7" r="4"></circle>
                                            <line x1="20" y1="8" x2="20" y2="14"></line>
                                            <line x1="23" y1="11" x2="17" y2="11"></line>
                                        </svg>
                                    </span>
                                </a>
                            </div>
                        </div>

                        <div class="card-list users-list"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Chats -->
        <div class="tab-pane fade h-100 show active" id="tab-content-chats" role="tabpanel">
            <div class="d-flex flex-column h-100 position-relative">
                <div class="hide-scrollbar">

                    <div class="container py-8">
                        <!-- Title -->
                        <div class="mb-8">
                            <h2 class="fw-bold m-0">Chats</h2>
                        </div>

                        <!-- Search -->
                        <div class="mb-6">
                            <form action="#">
                                <div class="input-group">
                                    <div class="input-group-text">
                                        <div class="icon icon-lg">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                                stroke-linecap="round" stroke-linejoin="round"
                                                class="feather feather-search">
                                                <circle cx="11" cy="11" r="8"></circle>
                                                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                                            </svg>
                                        </div>
                                    </div>

                                    <input type="text" class="form-control form-control-lg ps-0"
                                        placeholder="Search messages or users"
                                        aria-label="Search for messages or users...">
                                </div>
                            </form>
                        </div>

                        <!-- Chats -->
                        <div class="card-list conversations-list"></div>
                        <!-- Chats -->
                    </div>

                </div>
            </div>
        </div>
    </div>
</aside>
<!-- Sidebar -->
