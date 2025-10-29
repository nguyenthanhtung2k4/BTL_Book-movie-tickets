// === CAROUSEL SLIDESHOW - VÒNG TRÒN LIÊN TỤC ===
document.addEventListener("DOMContentLoaded", function () {
  
  //--- Bắt đầu Code Carousel (giữ nguyên) ---
  const slides = document.getElementById("slides");
  if (slides) {
      const slideItems = slides.children;
      const nextBtn = document.getElementById("nextSlide");
      const prevBtn = document.getElementById("prevSlide");

      if (slideItems.length > 1) {
          let currentIndex = 1; 
          const totalSlides = slideItems.length;

          const firstClone = slideItems[0].cloneNode(true);
          const lastClone = slideItems[totalSlides - 1].cloneNode(true);

          slides.appendChild(firstClone);
          slides.insertBefore(lastClone, slideItems[0]);

          const allSlides = slides.children;
          const totalClones = allSlides.length;
          const slideWidth = 100 / 3; 

          slides.style.transform = `translateX(-${currentIndex * slideWidth}%)`;

          function moveToSlide(index) {
            slides.style.transition = "transform 1.2s ease-in-out";
            slides.style.transform = `translateX(-${index * slideWidth}%)`;
          }

          function nextSlide() {
            if (currentIndex >= totalClones - 1) return; 
            currentIndex++;
            moveToSlide(currentIndex);

            if (currentIndex === totalClones - 1) {
              setTimeout(() => {
                slides.style.transition = "none";
                currentIndex = 1;
                slides.style.transform = `translateX(-${currentIndex * slideWidth}%)`;
              }, 1200);
            }
          }

          function prevSlide() {
            if (currentIndex <= 0) return;
            currentIndex--;
            moveToSlide(currentIndex);

            if (currentIndex === 0) {
              setTimeout(() => {
                slides.style.transition = "none";
                currentIndex = totalClones - 2;
                slides.style.transform = `translateX(-${currentIndex * slideWidth}%)`;
              }, 1200);
            }
          }

          setInterval(nextSlide, 5000);
          nextBtn.addEventListener("click", nextSlide);
          prevBtn.addEventListener("click", prevSlide);
      }
  }
  //--- Kết thúc Code Carousel ---


  // === Logic Lắng nghe nút Trailer Modal (Giữ nguyên) ===
  document.querySelectorAll('.open-trailer-modal').forEach(button => {
      button.addEventListener('click', (e) => {
          e.preventDefault();
          const url = e.currentTarget.getAttribute('data-trailer-url');
          if (url && url !== '#') {
              openTrailerModal(url);
          }
      });
  });

  // === CODE MỚI: Logic Lắng nghe nút Chi Tiết Phim ===
  document.querySelectorAll('.open-details-modal').forEach(button => {
      button.addEventListener('click', (e) => {
          e.preventDefault();
          const movieData = e.currentTarget.getAttribute('data-movie');
          if (movieData) {
              openDetailsModal(movieData);
          }
      });
  });

});
//--- Hết DOMContentLoaded ---


// === Các hàm cho Trailer Modal (Giữ nguyên) ===
function convertToEmbedUrl(url) {
    if (!url) return '';
    url = url.replace('http:', 'https:');
    const matchWatch = url.match(/(?:youtu\.be\/|youtube\.com\/(?:watch\?(?:.*&)?v=|(?:embed|v)\/))([a-zA-Z0-9_-]{11})/);
    if (matchWatch) {
        return `https://www.youtube.com/embed/${matchWatch[1]}?autoplay=1&mute=0`;
    }
    return '';
}

function openTrailerModal(trailerUrl) {
    const modal = document.getElementById('trailerModal');
    const content = document.getElementById('trailerContent');
    const embedUrl = convertToEmbedUrl(trailerUrl);

    if (embedUrl) {
        content.innerHTML = `
            <iframe 
                src="${embedUrl}" 
                frameborder="0" 
                allow="autoplay; encrypted-media; gyroscope;" 
                allowfullscreen>
            </iframe>
        `;
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    } else {
        alert('Rất tiếc, liên kết trailer không hợp lệ.');
    }
}

function closeTrailerModal(event) {
    const modal = document.getElementById('trailerModal');
    if (!event || event.target.id === 'trailerModal' || event.target.closest('button')) {
        document.getElementById('trailerContent').innerHTML = ''; 
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
}


// === CÁC HÀM MỚI CHO MODAL CHI TIẾT PHIM ===

// Hàm mở Modal Chi Tiết
function openDetailsModal(movieData) {
    const modal = document.getElementById('detailsModal');
    if (!modal) return;

    // Chuyển chuỗi JSON từ data-movie thành đối tượng
    const movie = JSON.parse(movieData);

    // Tìm các phần tử trong modal chi tiết
    const img = document.getElementById('modalDetailsImage');
    const title = document.getElementById('modalDetailsTitle');
    const rating = document.getElementById('modalDetailsRating');
    const duration = document.getElementById('modalDetailsDuration');
    const release = document.getElementById('modalDetailsRelease');
    const desc = document.getElementById('modalDetailsDesc');

    // Đổ dữ liệu vào modal
    img.src = movie.banner_url || '../../asset/img/no-banner.png';
    img.alt = movie.title || 'Poster';
    title.textContent = movie.title || 'Không có tiêu đề';
    
    rating.innerHTML = `⭐ ${movie.rating || 'N/A'}`;
    duration.innerHTML = `⏱️ ${movie.duration_min || 'N/A'} phút`;
    
    // Định dạng lại ngày tháng (dd/mm/yyyy)
    if (movie.release_date) {
        const date = new Date(movie.release_date);
        release.innerHTML = `📅 ${date.toLocaleDateString('vi-VN')}`;
    } else {
        release.innerHTML = '📅 N/A';
    }

    desc.textContent = movie.description || 'Chưa có mô tả cho phim này.';

    // Hiển thị modal
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

// Hàm đóng Modal Chi Tiết
function closeDetailsModal(event) {
    const modal = document.getElementById('detailsModal');
    // Chỉ đóng khi click vào nền (id="detailsModal") hoặc nút (closest('button'))
    if (!event || event.target.id === 'detailsModal' || event.target.closest('button')) {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
}