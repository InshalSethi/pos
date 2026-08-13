import axios from 'axios';

/**
 * Downloads an attachment file using Axios with responseType: 'blob'.
 * 
 * @param {string} url - API endpoint URL
 * @param {string} fallbackFileName - Default file name to save as
 */
export async function downloadAttachmentFile(url, fallbackFileName = 'attachment') {
  try {
    const response = await axios.get(url, {
      responseType: 'blob',
      headers: {
        'Accept': '*/*'
      }
    });

    // Check if error response returned as JSON Blob (e.g. 404 or 500)
    const blobType = response.data.type || '';
    if (blobType.includes('json') || blobType.includes('html')) {
      const text = await response.data.text();
      try {
        const json = JSON.parse(text);
        if (json.message) {
          alert(json.message);
          return;
        }
      } catch (e) {
        if (text.includes('Unauthenticated') || text.includes('404')) {
          alert('Failed to download file. Please check permissions or file availability.');
          return;
        }
      }
    }

    const contentType = response.headers['content-type'] || response.data.type || 'application/octet-stream';
    const blobData = response.data instanceof Blob 
      ? response.data.slice(0, response.data.size, contentType) 
      : new Blob([response.data], { type: contentType });

    const blobUrl = window.URL.createObjectURL(blobData);

    let fileName = fallbackFileName;
    const disposition = response.headers['content-disposition'];
    if (disposition && disposition.includes('filename=')) {
      const match = disposition.match(/filename="?([^";]+)"?/);
      if (match && match[1]) {
        fileName = match[1];
      }
    }

    const link = document.createElement('a');
    link.href = blobUrl;
    link.setAttribute('download', fileName);
    document.body.appendChild(link);
    link.click();
    link.remove();

    setTimeout(() => {
      window.URL.revokeObjectURL(blobUrl);
    }, 500);
  } catch (error) {
    console.error('Attachment download error:', error);
    if (error.response && error.response.data instanceof Blob) {
      try {
        const text = await error.response.data.text();
        const json = JSON.parse(text);
        alert(json.message || 'Failed to download attachment.');
        return;
      } catch (e) {
        // Fallback alert below
      }
    }
    alert('Failed to download attachment. File may not exist or permission denied.');
  }
}
