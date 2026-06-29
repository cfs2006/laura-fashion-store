    </main>

    <!-- 7. Footer -->
    <footer class="bg-brand-black text-white pt-20 pb-10 px-10 mt-auto">
        <div class="max-w-7xl mx-auto grid grid-cols-1 md:grid-cols-4 gap-12 border-b border-gray-800 pb-16">
            <div class="col-span-1 md:col-span-2">
                <h3 class="font-serif text-2xl mb-6">L'AURA</h3>
                <p class="text-gray-400 text-sm font-light mb-8 max-w-sm">Đăng ký nhận bản tin để cập nhật những bộ sưu tập mới nhất và ưu đãi độc quyền.</p>
                <form id="newsletter-form" class="flex flex-col sm:flex-row sm:items-center gap-4">
                    <input
                        type="email"
                        name="email"
                        id="newsletter-email"
                        placeholder="Email của bạn"
                        autocomplete="email"
                        required
                        class="bg-transparent border-b border-gray-600 px-0 py-2 text-sm focus:outline-none focus:border-brand-gold w-full sm:w-64 transition-colors"
                    >
                    <button
                        type="submit"
                        id="newsletter-submit"
                        class="uppercase text-xs tracking-widest hover:text-brand-gold transition-colors text-left sm:text-right disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        Đăng Ký
                    </button>
                </form>
            </div>
            <div>
                <h4 class="uppercase text-xs tracking-widest mb-6 font-bold"><a href="shop.php" class="hover:text-brand-gold transition-colors">Khám Phá</a></h4>
                <ul class="space-y-4 text-gray-400 text-sm font-light">
                    <li><a href="shop.php" class="hover:text-white transition-colors">Cửa Hàng</a></li>
                    <li><a href="about.php" class="hover:text-white transition-colors">Câu Chuyện</a></li>
                    <li><a href="careers.php" class="hover:text-white transition-colors">Tuyển Dụng</a></li>
                </ul>
            </div>
            <div>
                <h4 class="uppercase text-xs tracking-widest mb-6 font-bold"><a href="support.php" class="hover:text-brand-gold transition-colors">Hỗ Trợ</a></h4>
                <ul class="space-y-4 text-gray-400 text-sm font-light">
                    <li><a href="support.php" class="hover:text-white transition-colors">Liên Hệ</a></li>
                    <li><a href="support.php" class="hover:text-white transition-colors">Chính Sách Đổi Trả</a></li>
                    <li><a href="support.php" class="hover:text-white transition-colors">Câu Hỏi Thường Gặp</a></li>
                </ul>
            </div>
        </div>
        <div class="max-w-7xl mx-auto mt-8 flex flex-col md:flex-row justify-between items-center text-xs text-gray-500">
            <p>&copy; 2026 L'AURA. All rights reserved.</p>
            <div class="flex space-x-6 mt-4 md:mt-0">
                <a href="#" class="hover:text-white transition-colors"><i class="fab fa-instagram text-lg"></i></a>
                <a href="#" class="hover:text-white transition-colors"><i class="fab fa-facebook-f text-lg"></i></a>
                <a href="#" class="hover:text-white transition-colors"><i class="fab fa-twitter text-lg"></i></a>
            </div>
        </div>
    </footer>

    <!-- Chatbot Widget -->
    <div id="chatbot-widget" class="fixed bottom-24 right-6 z-50 flex flex-col items-end">
        <!-- Chat Window -->
        <div id="chat-window" class="hidden bg-white shadow-2xl rounded-2xl border border-gray-100 w-80 sm:w-96 mb-4 flex flex-col overflow-hidden transition-all duration-300 transform scale-95 opacity-0 origin-bottom-right">
            <!-- Header -->
            <div class="bg-brand-black text-white px-5 py-4 flex justify-between items-center">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-brand-gold rounded-full flex items-center justify-center text-sm font-bold">L</div>
                    <div>
                        <h4 class="font-bold text-sm">L'AURA Assistant</h4>
                        <p class="text-xs text-gray-300">Sẵn sàng hỗ trợ bạn</p>
                    </div>
                </div>
                <button id="close-chat" class="text-gray-300 hover:text-white transition-colors focus:outline-none">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <!-- Body -->
            <div id="chat-body" class="p-4 h-80 overflow-y-auto bg-gray-50 flex flex-col gap-3">
                <div class="flex gap-2 items-end">
                    <div class="bg-gray-200 text-brand-black px-4 py-2 rounded-2xl rounded-bl-sm text-sm max-w-[85%]">
                        Chào bạn! Mình là trợ lý ảo của L'AURA. Mình có thể giúp gì cho bạn hôm nay?
                    </div>
                </div>
            </div>

            <!-- Footer (Input) -->
            <div class="p-3 bg-white border-t border-gray-100 flex gap-2">
                <input type="text" id="chat-input" placeholder="Nhập tin nhắn..." class="flex-1 bg-gray-100 text-sm px-4 py-2 rounded-full border-none focus:outline-none focus:ring-1 focus:ring-brand-gold">
                <button id="send-chat" class="w-10 h-10 bg-brand-gold text-white rounded-full flex items-center justify-center hover:bg-brand-black transition-colors focus:outline-none">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </div>
        </div>

        <!-- Chat Bubble -->
        <button id="chat-bubble" class="w-14 h-14 bg-brand-black text-white rounded-full shadow-lg flex items-center justify-center hover:scale-110 hover:bg-brand-gold transition-all duration-300 focus:outline-none border-2 border-brand-gold">
            <i class="fas fa-comment-dots text-2xl"></i>
        </button>
    </div>

    <!-- Chatbot JS -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const chatbotWidget = document.getElementById('chatbot-widget');
            const chatBubble = document.getElementById('chat-bubble');
            const chatWindow = document.getElementById('chat-window');
            const closeChat = document.getElementById('close-chat');
            const chatInput = document.getElementById('chat-input');
            const sendChat = document.getElementById('send-chat');
            const chatBody = document.getElementById('chat-body');

            const toggleChat = () => {
                if (chatWindow.classList.contains('hidden')) {
                    chatWindow.classList.remove('hidden');
                    void chatWindow.offsetWidth;
                    chatWindow.classList.remove('scale-95', 'opacity-0');
                    chatWindow.classList.add('scale-100', 'opacity-100');
                    chatInput.focus();
                } else {
                    chatWindow.classList.remove('scale-100', 'opacity-100');
                    chatWindow.classList.add('scale-95', 'opacity-0');
                    setTimeout(() => chatWindow.classList.add('hidden'), 300);
                }
            };

            // Drag logic
            let isDragging = false;
            let currentX, currentY, initialX, initialY;
            let xOffset = 0, yOffset = 0;
            let wasDragged = false;

            function dragStart(e) {
                wasDragged = false;
                if (e.type === "touchstart") {
                    initialX = e.touches[0].clientX - xOffset;
                    initialY = e.touches[0].clientY - yOffset;
                } else {
                    initialX = e.clientX - xOffset;
                    initialY = e.clientY - yOffset;
                }
                // Only start drag if clicking on the bubble, not the chat window
                if (e.target === chatBubble || chatBubble.contains(e.target)) {
                    isDragging = true;
                }
            }

            function drag(e) {
                if (isDragging) {
                    if (e.type === "touchmove") {
                        currentX = e.touches[0].clientX - initialX;
                        currentY = e.touches[0].clientY - initialY;
                    } else {
                        currentX = e.clientX - initialX;
                        currentY = e.clientY - initialY;
                    }
                    
                    if (Math.abs(currentX - xOffset) > 2 || Math.abs(currentY - yOffset) > 2) {
                        wasDragged = true;
                        if(e.cancelable) e.preventDefault();
                    }

                    xOffset = currentX;
                    yOffset = currentY;
                    chatbotWidget.style.transform = `translate3d(${currentX}px, ${currentY}px, 0)`;
                }
            }

            function dragEnd(e) {
                if(isDragging) {
                    initialX = currentX;
                    initialY = currentY;
                    isDragging = false;
                }
            }

            chatBubble.addEventListener('mousedown', dragStart);
            document.addEventListener('mousemove', drag, {passive: false});
            document.addEventListener('mouseup', dragEnd);
            
            chatBubble.addEventListener('touchstart', dragStart, {passive: true});
            document.addEventListener('touchmove', drag, {passive: false});
            document.addEventListener('touchend', dragEnd);

            chatBubble.addEventListener('click', (e) => {
                if (wasDragged) {
                    wasDragged = false;
                    e.preventDefault();
                    return;
                }
                toggleChat();
            });
            closeChat.addEventListener('click', toggleChat);

            const addMessage = (text, isUser = false) => {
                const msgDiv = document.createElement('div');
                msgDiv.className = `flex gap-2 items-end ${isUser ? 'justify-end' : ''}`;
                
                const bubble = document.createElement('div');
                bubble.className = `px-4 py-2 rounded-2xl text-sm max-w-[85%] ${
                    isUser 
                    ? 'bg-brand-black text-white rounded-br-sm' 
                    : 'bg-gray-200 text-brand-black rounded-bl-sm'
                }`;
                // simple sanitize
                bubble.textContent = text;
                
                msgDiv.appendChild(bubble);
                chatBody.appendChild(msgDiv);
                chatBody.scrollTop = chatBody.scrollHeight;
            };

            const addTyping = () => {
                const msgDiv = document.createElement('div');
                msgDiv.id = 'typing-indicator';
                msgDiv.className = `flex gap-2 items-end`;
                msgDiv.innerHTML = `<div class="bg-gray-200 text-brand-black px-4 py-2 rounded-2xl rounded-bl-sm text-sm max-w-[85%] flex gap-1 items-center">
                    <div class="w-1.5 h-1.5 bg-gray-500 rounded-full animate-bounce"></div>
                    <div class="w-1.5 h-1.5 bg-gray-500 rounded-full animate-bounce" style="animation-delay: 0.1s"></div>
                    <div class="w-1.5 h-1.5 bg-gray-500 rounded-full animate-bounce" style="animation-delay: 0.2s"></div>
                </div>`;
                chatBody.appendChild(msgDiv);
                chatBody.scrollTop = chatBody.scrollHeight;
            };

            const removeTyping = () => {
                const el = document.getElementById('typing-indicator');
                if (el) el.remove();
            };

            const handleSend = async () => {
                const text = chatInput.value.trim();
                if (!text) return;

                chatInput.value = '';
                addMessage(text, true);
                addTyping();

                try {
                    // Make absolute path just in case we are in subdirs
                    const basePath = window.location.pathname.includes('/admin/') ? '../' : '';
                    const response = await fetch(basePath + 'chatbot_api.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ message: text })
                    });
                    const rawText = await response.text();
                    try {
                        const data = JSON.parse(rawText);
                        removeTyping();
                        if (data.reply) {
                            addMessage(data.reply);
                        } else {
                            addMessage("Xin lỗi, mình không thể trả lời lúc này.");
                        }
                    } catch (e) {
                        removeTyping();
                        addMessage("Hệ thống AI hiện đang quá tải hoặc bị chặn bởi tường lửa bảo mật của máy chủ. Vui lòng thử lại sau.");
                    }
                } catch (e) {
                    removeTyping();
                    addMessage("Lỗi: " + e.message);
                }
            };

            sendChat.addEventListener('click', handleSend);
            chatInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') handleSend();
            });
        });
    </script>

    <!-- JS Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/ScrollTrigger.min.js"></script>
    <!-- Thư viện hỗ trợ hiệu ứng tilt 3D (VanillaTilt) -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/vanilla-tilt/1.8.0/vanilla-tilt.min.js"></script>
    
    <!-- Custom Main JS -->
    <script src="js/main.js"></script>
    <!-- Original script.js -->
    <script src="script.js"></script>
</body>
</html>
